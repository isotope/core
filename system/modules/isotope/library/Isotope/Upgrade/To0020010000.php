<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


class To0020010000 extends Assistant
{

    protected $objGaleries;

    public function run($blnInstalled)
    {
        if ($blnInstalled && \Database::getInstance()->tableExists('tl_iso_gallery')) {
            $this->createDatabaseField('lightbox_template', 'tl_iso_gallery');

            $t = \Isotope\Model\Gallery::getTable();
            $this->objGaleries = \Isotope\Model\Gallery::findBy(
                array("$t.type='standard'", "$t.anchor='lightbox'", "lightbox_template IS NULL"),
                null
            );

            if (null !== $this->objGaleries) {
                $this->generate();
            }
        }
    }

    public function compile()
    {
        \System::loadLanguageFile('tl_iso_gallery');

        $strBuffer = '
<table style="width:100%">
<thead>
    <tr>
        <th>' . $GLOBALS['TL_LANG']['tl_iso_gallery']['name'][0] . '</th>
        <th>' . $GLOBALS['TL_LANG']['tl_iso_gallery']['lightbox_template'][0] . '<span class="mandatory">*</span></th>
    </tr>
</thead>
<tbody>';

        foreach ($this->objGaleries as $objGallery) {

            $objSelect = new \SelectMenu(\Widget::getAttributesFromDca(array(
                'options' => array_merge(
                    \Controller::getTemplateGroup('moo_'),
                    \Controller::getTemplateGroup('j_')
                ),
                'eval' => array('includeBlankOption'=>true, 'mandatory'=>true)
            ), 'gallery['.$objGallery->id.']'));

            if (\Input::post('FORM_SUBMIT') == 'tl_iso_upgrade_20010000') {
                $objSelect->validate();

                if (!$objSelect->hasErrors()) {
                    $objGallery->lightbox_template = serialize(array($objSelect->value));
                    $objGallery->save();
                }
            }

            $strBuffer .= '
    <tr>
        <td>' . $objGallery->name . '</td>
        <td>' . $objSelect->generateWithError() . '</td>
    </tr>';
        }

        $strBuffer .= '
</tbody>
</table>';

        $this->Template->formSubmit = 'tl_iso_upgrade_20010000';
        $this->Template->fields = $strBuffer;
        $this->Template->matter = $GLOBALS['TL_LANG']['UPG']['20010000'];

        if (\Input::post('FORM_SUBMIT') == 'tl_iso_upgrade_20010000') {
            \Controller::reload();
        }
    }
}
