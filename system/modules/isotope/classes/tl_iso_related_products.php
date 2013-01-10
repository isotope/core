<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_iso_related_products
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_related_products extends \Backend
{

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @return string
     */
    public function listRows($row)
    {
        $strCategory = $this->Database->prepare("SELECT * FROM tl_iso_related_categories WHERE id=?")->execute($row['category'])->name;

        $strBuffer = '
<div class="cte_type" style="color:#666966"><strong>' . $GLOBALS['TL_LANG']['tl_iso_related_products']['category'][0] . ':</strong> ' . $strCategory . '</div>';

        $arrProducts = deserialize($row['products']);

        if (is_array($arrProducts) && !empty($arrProducts))
        {
            $strBuffer .= '<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h0' : '') . ' block"><ul>';
            $objProducts = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id IN (" . implode(',', $arrProducts) . ") ORDER BY name");

            while ($objProducts->next())
            {
                $strBuffer .= '<li>' . $objProducts->name . '</li>';
            }

            $strBuffer .= '</ul></div>' . "\n";
        }

        return $strBuffer;
    }


    /**
     * Initialize the data container
     * @param object
     * @return string
     */
    public function initDCA($dc)
    {
        $arrCategories = array();
        $objCategories = $this->Database->prepare("SELECT * FROM tl_iso_related_categories WHERE id NOT IN (SELECT category FROM tl_iso_related_products WHERE pid=" . (strlen(\Input::get('act')) ? "(SELECT pid FROM tl_iso_related_products WHERE id=?) AND id!=?" : '?') . ")")
                                        ->execute($dc->id, $dc->id);

        while ($objCategories->next())
        {
            $arrCategories[$objCategories->id] = $objCategories->name;
        }

        if (empty($arrCategories))
        {
            $GLOBALS['TL_DCA']['tl_iso_related_products']['config']['closed'] = true;
        }

        if (\Input::get('act') == 'edit')
        {
            unset($GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['category']['foreignKey']);
            $GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['category']['options'] = $arrCategories;
            $GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['products']['eval']['allowedIds'] = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE pid=0 AND id!=(SELECT pid FROM tl_iso_related_products WHERE id=?)")->execute($dc->id)->fetchEach('id');
        }
    }
}
