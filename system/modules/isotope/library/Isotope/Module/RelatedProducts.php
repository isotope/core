<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Module;


/**
 * Class ModuleIsotopeRelatedProducts
 *
 * List products related to the current product reader.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class RelatedProducts extends ProductList
{

    /**
     * Do not cache related products cause the list is different depending on URL parameters
     * @var boolean
     */
    protected $blnCacheProducts = false;


    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: RELATED PRODUCTS ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (!\Isotope\Frontend::getAutoItem('product'))
        {
            return '';
        }

        $this->iso_related_categories = deserialize($this->iso_related_categories);

        if (!is_array($this->iso_related_categories) || !count($this->iso_related_categories)) // Can't use empty() because its an object property (using __get)
        {
            return '';
        }

        return parent::generate();
    }


    /**
     * Find all products we need to list.
     * @return array
     */
    protected function findProducts($arrCacheIds=null)
    {
        $strAlias = \Isotope\Frontend::getAutoItem('product');
        $arrIds = array(0);
        $arrJumpTo = array();

        $objCategories = \Database::getInstance()->prepare("SELECT *, (SELECT jumpTo FROM tl_iso_related_categories WHERE id=category) AS jumpTo FROM tl_iso_related_products WHERE pid IN (SELECT id FROM tl_iso_products WHERE " . (is_numeric($strAlias) ? 'id' : 'alias') . "=?" . ($this->iso_list_where != '' ? ' AND '.$this->iso_list_where : '') . ") AND category IN (" . implode(',', $this->iso_related_categories) . ") ORDER BY id=" . implode(' DESC, id=', $this->iso_related_categories) . " DESC")->execute($strAlias);

        while ($objCategories->next())
        {
            $ids = deserialize($objCategories->products);

            if (is_array($ids) && !empty($ids))
            {
                $arrIds = array_unique(array_merge($arrIds, $ids));

                if ($objCategories->jumpTo)
                {
                    $arrJumpTo = array_fill_keys($ids, $objCategories->jumpTo) + $arrJumpTo;
                }
            }
        }

        return \Isotope\Frontend::getProducts($arrIds);
    }
}
