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
use Contao\Database;
use Contao\Session;
use Contao\System;

class Panel extends Backend
{

    /**
     * Generate product filter buttons and return them as HTML
     * @return string
     */
    public static function generateFilterButtons(): string
    {
        $user = BackendUser::getInstance();
        $session = Session::getInstance()->getData();
        $intProduct = $session['filter']['tl_iso_product_collection']['iso_product'] ?? '';
        $buttons = [];

        // Check if user can manage products
        if ($user->hasAccess('page', 'modules')) {
            $buttons[] = '
    <a href="' . ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('dc.tl_iso_product', ['fieldType' => 'radio'])) . '" class="tl_submit'.($intProduct ? ' active' : '').'" id="productFilter">' . ($GLOBALS['TL_LANG']['MSC']['filterByProducts'] ?? '') . '</a>
    <script>
      document.getElementById("productFilter").addEventListener("click", function(e) {
        e.preventDefault();
        Backend.openModalSelector({
          id: "tl_listing",
          title: ' . json_encode($GLOBALS['TL_LANG']['MOD']['iso_products'][0]) . ',
          url: this.href+'.json_encode($intProduct).',
          callback: function(table, value) {
            new Request.Contao({
              evalScripts: false,
              onRequest: AjaxRequest.displayBox(Contao.lang.loading + \' …\')
            }).post({action:"filterProducts", value:value[0], REQUEST_TOKEN:"' . REQUEST_TOKEN . '"});
          }
        })
      });
    </script>';
        }

        return '
<style>
@media (min-width: 768px) {
    .tl_filter {
        display: flex;
        align-items: center;
        width: 80%;
        margin-left: 2em;
    }
    .tl_filter .tl_select {
        max-width: none;
    }
}
@media (min-width: 1200px) {
    .tl_filter {
        width: 60%;
    }
}
</style>
<div class="iso_filter tl_subpanel">
' . implode("\n", $buttons) . '
</div>';
    }

    /**
     * Apply advanced filters to product list view
     * @return void
     */
    public function applyAdvancedFilters(): void
    {
        $session = Session::getInstance()->getData();

        if (empty($session['filter']['tl_iso_product_collection']['iso_product'])) {
            return;
        }

        $productId = $session['filter']['tl_iso_product_collection']['iso_product'];

        $objProducts = Database::getInstance()->prepare(<<<SQL
            SELECT c.id
            FROM tl_iso_product_collection c
            JOIN tl_iso_product_collection_item i ON i.pid=c.id
            JOIN tl_iso_product v ON i.product_id=v.id AND v.language=''
            JOIN tl_iso_product p ON v.pid=p.id AND p.language=''
            WHERE
                c.type='order'
                AND (i.product_id=? OR p.id=?)
SQL
        )->execute($productId, $productId);

        $collectionIds = array_unique($objProducts->fetchEach('id'));

        if (empty($collectionIds)) {
            $collectionIds = array(0);
        }

        $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['root'] = $collectionIds;
    }
}
