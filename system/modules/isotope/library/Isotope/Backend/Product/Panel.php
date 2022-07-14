<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use Isotope\Model\Group;

class Panel extends Backend
{

    /**
     * Generate product filter buttons and return them as HTML
     * @return string
     */
    public static function generateFilterButtons()
    {
        if (Input::get('id') > 0) {
            return '';
        }

        $user      = BackendUser::getInstance();
        $session   = Session::getInstance()->getData();
        $intPage   = $session['filter']['tl_iso_product']['iso_page'] ?? 0;
        $buttons   = [];

        // Check if user can manage groups
        if ($user->isAdmin || (\is_array($user->iso_groups) && 0 !== \count($user->iso_groups))) {
            $buttons[] = '
    <a href="' . ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('dc.tl_iso_group', ['fieldType' => 'radio'])) . '" class="tl_submit'.(!empty($session['iso_products_gid']) ? ' active' : '').'" id="groupFilter">' . $GLOBALS['TL_LANG']['MSC']['filterByGroups'] . '</a>
    <script>
      document.getElementById("groupFilter").addEventListener("click", function(e) {
        e.preventDefault();
        Backend.openModalSelector({
          id: "tl_listing",
          title: ' . json_encode($GLOBALS['TL_LANG']['tl_iso_product']['product_groups'][0]) . ',
          url: this.href+'.json_encode($session['iso_products_gid'] ?? '').',
          callback: function(table, value) {
            new Request.Contao({
              evalScripts: false,
              onRequest: AjaxRequest.displayBox(Contao.lang.loading + \' …\')
            }).post({action:"filterGroups", value:value[0], REQUEST_TOKEN:"' . REQUEST_TOKEN . '"});
          }
        })
      });
    </script>';
        }

        $buttons[] = '
    <a href="' . ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('dc.tl_page', ['fieldType' => 'radio'])) . '" class="tl_submit'.($intPage > 0 ? ' active' : '').'" id="pageFilter">' . $GLOBALS['TL_LANG']['MSC']['filterByPages'] . '</a>
    <script>
      document.getElementById("pageFilter").addEventListener("click", function(e) {
        e.preventDefault();
        Backend.openModalSelector({
          id: "tl_listing",
          title: ' . json_encode($GLOBALS['TL_LANG']['MOD']['page'][0]) . ',
          url: this.href+'.json_encode($intPage).',
          callback: function(table, value) {
            new Request.Contao({
              evalScripts: false,
              onRequest: AjaxRequest.displayBox(Contao.lang.loading + \' …\')
            }).post({action:"filterPages", value:value[0], REQUEST_TOKEN:"' . REQUEST_TOKEN . '"});
          }
        })
      });
    </script>';

        return '
<div class="iso_filter tl_subpanel">
' . implode("\n", $buttons) . '
</div>';
    }

    /**
     * Generate advanced filter panel and return them as HTML
     * @return string
     */
    public static function generateAdvancedFilters()
    {
        if (Input::get('id') > 0) {
            return '';
        }

        $session = Session::getInstance()->getData();

        // Filters
        $arrFilters = [
            'iso_noimages'   => [
                'name'    => 'iso_noimages',
                'label'   => $GLOBALS['TL_LANG']['tl_iso_product']['filter_noimages'],
                'options' => ['' => $GLOBALS['TL_LANG']['MSC']['no'], 1 => $GLOBALS['TL_LANG']['MSC']['yes']],
            ],
            'iso_nocategory' => [
                'name'    => 'iso_nocategory',
                'label'   => $GLOBALS['TL_LANG']['tl_iso_product']['filter_nocategory'],
                'options' => ['' => $GLOBALS['TL_LANG']['MSC']['no'], 1 => $GLOBALS['TL_LANG']['MSC']['yes']],
            ],
            'iso_new'        => [
                'name'    => 'iso_new',
                'label'   => $GLOBALS['TL_LANG']['tl_iso_product']['filter_new'],
                'options' => [
                    'new_today' => $GLOBALS['TL_LANG']['tl_iso_product']['filter_new_today'],
                    'new_week'  => $GLOBALS['TL_LANG']['tl_iso_product']['filter_new_week'],
                    'new_month' => $GLOBALS['TL_LANG']['tl_iso_product']['filter_new_month'],
                ],
            ],
        ];

        $strBuffer = '
<div class="tl_advanced_filter iso_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['tl_iso_product']['filter'] . '</strong>' . "\n";

        // Generate filters
        foreach ($arrFilters as $arrFilter) {
            $strOptions = '
  <option value="' . $arrFilter['name'] . '">' . $arrFilter['label'] . '</option>
  <option value="' . $arrFilter['name'] . '">---</option>' . "\n";

            // Generate options
            foreach ($arrFilter['options'] as $k => $v) {
                $strOptions .= '  <option value="' . $k . '"' . ((($session['filter']['tl_iso_product'][$arrFilter['name']] ?? null) === (string) $k) ? ' selected' : '') . '>' . $v . '</option>' . "\n";
            }

            $strBuffer .= '<select name="' . $arrFilter['name'] . '" id="' . $arrFilter['name'] . '" class="tl_select' . (isset($session['filter']['tl_iso_product'][$arrFilter['name']]) ? ' active' : '') . '">
' . $strOptions . '
</select>' . "\n";
        }

        return $strBuffer . '</div>';
    }

    /**
     * Generate icon to open the page picker for manual sorting
     */
    public function generateSortingIcon()
    {
        if (Input::get('id') > 0) {
            return '';
        }

        return '
<div class="tl_subpanel tl_iso_category_sorting">
<a href="#" onclick="Backend.getScrollOffset();Isotope.openModalPageSelector({\'width\':765,\'title\':\'' . StringUtil::specialchars($GLOBALS['TL_LANG']['MOD']['page'][0]) . '\',\'url\':\'contao/page.php?do=' . Input::get('do') . '&amp;table=tl_iso_product_category&amp;field=page_id&amp;value=0\',\'action\':\'sortByPage\'});return false" title="' . $GLOBALS['TL_LANG']['tl_iso_product']['sorting'] . '">' . Image::getHtml('page.svg', $GLOBALS['TL_LANG']['tl_iso_product']['sorting']) . '</a>
</div>';
    }

    /**
     * Apply advanced filters to product list view
     * @return void
     */
    public function applyAdvancedFilters()
    {
        $session = Session::getInstance()->getData();

        // Store filter values in the session
        foreach ($_POST as $k => $v) {
            if (substr($k, 0, 4) != 'iso_') {
                continue;
            }

            // Reset the filter
            if ($k == Input::post($k)) {
                unset($session['filter']['tl_iso_product'][$k]);
            } // Apply the filter
            else {
                $session['filter']['tl_iso_product'][$k] = Input::post($k);
            }
        }

        Session::getInstance()->setData($session);

        if (Input::get('id') > 0 || !isset($session['filter']['tl_iso_product'])) {
            return;
        }

        $arrProducts = null;

        // Filter the products
        foreach ($session['filter']['tl_iso_product'] as $k => $v) {
            if (substr($k, 0, 4) != 'iso_') {
                continue;
            }

            switch ($k) {
                // Show products with or without images
                case 'iso_noimages':
                    $objProducts = Database::getInstance()->execute("SELECT id FROM tl_iso_product WHERE language='' AND images " . ($v ? 'IS NULL' : 'IS NOT NULL'));
                    $arrProducts = \is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                    break;

                // Show products with or without category
                case 'iso_nocategory':
                    $objProducts = Database::getInstance()->execute("SELECT id FROM tl_iso_product p WHERE pid=0 AND language='' AND (SELECT COUNT(*) FROM tl_iso_product_category c WHERE c.pid=p.id)" . ($v ? '=0' : '>0'));
                    $arrProducts = \is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                    break;

                // Show new products
                case 'iso_new':
                    $date = 0;

                    switch ($v) {
                        case 'new_today':
                            $date = strtotime('-1 day');
                            break;

                        case 'new_week':
                            $date = strtotime('-1 week');
                            break;

                        case 'new_month':
                            $date = strtotime('-1 month');
                            break;
                    }

                    $objProducts = Database::getInstance()->prepare("SELECT id FROM tl_iso_product WHERE language='' AND dateAdded>=?")->execute($date);
                    $arrProducts = \is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                    break;

                case 'iso_page':
                    // Filter the products by pages
                    if ($v > 0) {
                        $objProducts = Database::getInstance()->execute("SELECT id FROM tl_iso_product p WHERE pid=0 AND language='' AND id IN (SELECT pid FROM tl_iso_product_category c WHERE c.pid=p.id AND c.page_id=" . (int) $v . ')');
                        $arrProducts = \is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                    }
                    break;

                default:
                    // !HOOK: add custom advanced filters
                    if (isset($GLOBALS['ISO_HOOKS']['applyAdvancedFilters']) && \is_array($GLOBALS['ISO_HOOKS']['applyAdvancedFilters'])) {
                        foreach ($GLOBALS['ISO_HOOKS']['applyAdvancedFilters'] as $callback) {
                            $arrReturn = System::importStatic($callback[0])->{$callback[1]}($k);

                            if (\is_array($arrReturn)) {
                                $arrProducts = \is_array($arrProducts) ? array_intersect($arrProducts, $arrReturn) : $arrReturn;
                                break;
                            }
                        }
                    }

                    System::log('Advanced product filter "' . $k . '" not found.', __METHOD__, TL_ERROR);
                    break;
            }
        }

        if (\is_array($arrProducts) && empty($arrProducts)) {
            $arrProducts = array(0);
        }

        $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['root'] = $arrProducts;
    }
}
