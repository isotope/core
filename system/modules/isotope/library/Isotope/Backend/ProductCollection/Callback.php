<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductCollection;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\Database;
use Contao\DataContainer;
use Contao\DcaExtractor;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\SelectMenu;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Document\DocumentInterface;
use Isotope\CoreBundle\Model\Document;
use Isotope\CoreBundle\ModelType\DocumentRegistry;
use Isotope\Frontend;
use Isotope\Haste\Util\Format;
use Isotope\Model\ProductCollection\Order;
use NotificationCenter\Model\Notification;


class Callback extends Backend
{
    /**
     * @param DataContainer $dc
     */
    public function generateOrderShow($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null === $objOrder) {
            Controller::redirect('contao/main.php?act=error');
        }

        $arrRelations = DcaExtractor::getInstance('tl_iso_product_collection')->getRelations();

        $strBuffer = '
<div>
<table class="tl_show">
  <tbody>';

        foreach ($GLOBALS['TL_DCA']['tl_iso_product_collection']['fields'] as $field => $config) {
            if (isset($config['eval']['doNotShow']) && $config['eval']['doNotShow']) {
                continue;
            }

            $operations = [];

            if (isset($arrRelations[$field]['table']) && 'tl_iso_product_collection' !== $arrRelations[$field]['table'] && $dc->activeRecord->{$field}) {
                $relatedTable = $arrRelations[$field]['table'];
                $module = null;
                $name = null;

                $objRecord = Database::getInstance()->prepare("SELECT id FROM $relatedTable WHERE id=?")->execute($dc->activeRecord->{$field});

                if ($objRecord->numRows) {
                    foreach ($GLOBALS['BE_MOD'] as $modules) {
                        foreach ($modules as $moduleName => $moduleConfig) {
                            if (isset($moduleConfig['tables']) && \in_array($relatedTable, $moduleConfig['tables'], true)) {
                                $module = $moduleConfig;
                                $name = $moduleName;
                                break;
                            }
                        }
                    }

                    if ($module && BackendUser::getInstance()->hasAccess($name, 'modules')) {
                        System::loadLanguageFile($relatedTable);
                        Controller::loadDataContainer($relatedTable);

                        if (isset($GLOBALS['TL_DCA'][$relatedTable]['list']['operations']['show'])) {
                            $title = $GLOBALS['TL_DCA'][$relatedTable]['list']['operations']['show']['label'] ?? '';
                            $title = \is_array($title) ? ($title[0] ?? '') : $title;
                            $title = sprintf($title, $dc->activeRecord->{$field});
                            $operations[] = sprintf(
                                '<a href="%s" title="%s" onclick="Backend.openModalIframe({\'title\':\'%s\',\'url\':this.href});return false" class="%s">%s</a>',
                                System::getContainer()->get('router')->generate('contao_backend', [
                                    'do' => $name,
                                    'table' => $relatedTable,
                                    'act' => 'show',
                                    'id' => $dc->activeRecord->{$field},
                                    'popup' => 1,
                                    'rt' => \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(),
                                ]),
                                $title,
                                $title,
                                $name,
                                Image::getHtml('show')
                            );
                        }

                        if (
                            isset($GLOBALS['TL_DCA'][$relatedTable]['list']['operations']['edit'])
                            && BackendUser::getInstance()->canEditFieldsOf($relatedTable)
                        ) {
                            array_unshift($operations, sprintf(
                                '<a href="%s" title="%s" class="%s">%s</a>',
                                System::getContainer()->get('router')->generate('contao_backend', [
                                    'do' => $name,
                                    'table' => $relatedTable,
                                    'act' => 'edit',
                                    'id' => $dc->activeRecord->{$field},
                                    'rt' => \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(),
                                ]),
                                sprintf($GLOBALS['TL_DCA'][$relatedTable]['list']['operations']['edit']['label'][0], $dc->activeRecord->{$field}),
                                $name,
                                Image::getHtml('edit')
                            ));
                        }
                    }
                }
            }

            $strBuffer .= '
  <tr>
    <td class="tl_label">' . Format::dcaLabel($dc->table, $field) . ' <small>'.$field.'</small></td>
    <td>' . Format::dcaValue($dc->table, $field, $objOrder->{$field}, $dc) . '</td>
    <td>' . implode(' ', $operations) . '</td>
  </tr>';
        }

        return $strBuffer . '
</tbody></table>
</div>';
    }

    /**
     * Generate the order details view when editing an order
     *
     * @param object $dc
     *
     * @return string
     *
     * @deprecated  we should probably remove this in 3.0 as it does no longer make sense
     */
    public function generateEmailData($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null === $objOrder) {
            throw new InternalServerErrorException('Order ID '.$dc->id.' not found');
        }

        $arrEmail = StringUtil::deserialize($objOrder->email_data, true);

        if (empty($arrEmail) || !\is_array($arrEmail)) {
            return '<div class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_product_collection']['noEmailData'] . '</div>';
        }

        $strBuffer = '
<div>
<table class="tl_show">
  <tbody>';

        foreach ($arrEmail as $k => $v) {
            if (\is_array($v)) {
                $strValue = implode(', ', $v);
            } else {
                $strValue = ((strip_tags($v) == $v) ? nl2br($v) : $v);
            }

            $strBuffer .= '
  <tr>
    <td class="tl_label">' . ($GLOBALS['TL_LANG']['tl_iso_product_collection']['emailData'][$k] ?? $k) . ': <small>'.$k.'</small></td>
    <td>' . $strValue . '</td>
  </tr>';
        }

        return $strBuffer . '
</tbody></table>
</div>';
    }

    /**
     * Return the print button if there is at least one document
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function printButton($row, $href, $label, $title, $icon, $attributes)
    {
        static $hasDocuments = null;

        if (null === $hasDocuments) {
            $hasDocuments = Database::getInstance()->execute('SELECT COUNT(*) AS count FROM tl_iso_document')->count > 0;
        }

        return $hasDocuments ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : '';
    }

    /**
     * Pass an order to the document
     *
     * @throws \Exception
     * @return string
     */
    public function printDocument(DataContainer $dc)
    {
        $strRedirectUrl = str_replace('&key=print_document', '', Environment::get('request'));

        if ('tl_iso_print_document' === Input::post('FORM_SUBMIT')) {
            if (($objOrder = Order::findByPk($dc->id)) === null) {
                Message::addError('Could not find order id.');
                Controller::redirect($strRedirectUrl);
            }

            Frontend::loadOrderEnvironment($objOrder);

            if (($documentModel = Document::findByPk(Input::post('document'))) === null) {
                Message::addError('Could not find document id.');
                Controller::redirect($strRedirectUrl);
            }

            /** @var DocumentInterface $document */
            $document = System::getContainer()->get(DocumentRegistry::class)->get($documentModel);
            $document->outputToBrowser($objOrder);
        }

        $arrSelect = array
        (
            'name'       => 'document',
            'label'      => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_choice'],
            'inputType'  => 'select',
            'foreignKey' => 'tl_iso_document.name',
            'eval'       => array('mandatory' => true),
        );

        $arrSelect = SelectMenu::getAttributesFromDca($arrSelect, $arrSelect['name']);

        if (\count($arrSelect['options'] ?? []) > 1) {
            array_unshift($arrSelect['options'], ['value' => '', 'label' => '-']);
        }

        $objSelect = new SelectMenu($arrSelect);

        $strMessages = Message::generate();
        Message::reset();

        // Return form
        return '
<div id="tl_buttons">
<a href="' . \Contao\StringUtil::ampersand($strRedirectUrl) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document'][1], $dc->id) . '</h2>' . $strMessages . '

<form id="tl_iso_product_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_print_document">
<input type="hidden" name="REQUEST_TOKEN" value="' . \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue() . '">

<div class="block tl_tbox">
  <div class="clr widget">
    ' . $objSelect->parse() . '
    <p class="tl_help">' . $objSelect->description . '</p>
  </div>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="print" id="print" class="tl_submit" alt="" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']) . '">
</div>

</div>
</form>';
    }
}
