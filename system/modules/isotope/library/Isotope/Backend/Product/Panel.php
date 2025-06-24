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
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\RequestToken;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class Panel extends Backend
{
    /**
     * Generate icon to open the page picker for manual sorting
     */
    public function generateSortingIcon()
    {
        if (Input::get('id') > 0) {
            return '';
        }

        $target = System::getContainer()->get('router')->generate('contao_backend', [
            'do' => 'iso_products',
            'table' => 'tl_iso_product_category',
            'id' => '_value_',
            'page_id' => '_value_',
            'rt' => \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(),
        ]);

        return '
<div class="tl_subpanel tl_iso_category_sorting">
<a href="' . \Contao\StringUtil::ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('dc.tl_page')) . '" id="tl_iso_category_sorting" title="' . $GLOBALS['TL_LANG']['tl_iso_product']['sorting'] . '">' . Image::getHtml('pagemounts.svg', $GLOBALS['TL_LANG']['tl_iso_product']['sorting']) . '</a></p>
    <script>
      $("tl_iso_category_sorting").addEvent("click", function(e) {
        e.preventDefault();
        Backend.openModalSelector({
          "id": "tl_listing",
          "title": ' . json_encode($GLOBALS['TL_LANG']['MOD']['page'][0]) . ',
          "url": this.href,
          "callback": function (table, value) {
            window.location.href = "'.$target.'".replace(\'_value_\', value);
          }
        });
      });
    </script>
</div>';
    }
}
