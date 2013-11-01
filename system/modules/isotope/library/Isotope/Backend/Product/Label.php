<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Backend\Product;

use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;


class Label extends \Backend
{

    /**
     * Generate a product label and return it as HTML string
     * @param array
     * @param string
     * @param object
     * @param array
     * @return string
     */
    public function generate($row, $label, $dc, $args)
    {
        $objProduct = Product::findByPk($row['id']);

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
            switch ($field) {

                // Add an image
                case 'images':
                    $arrImages = deserialize($objProduct->images);
                    $args[$i] = '&nbsp;';

                    if (is_array($arrImages) && !empty($arrImages)) {
                        foreach ($arrImages as $image) {
                            $strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];

                            if (!is_file(TL_ROOT . '/' . $strImage)) {
                                continue;
                            }

                            $size = @getimagesize(TL_ROOT . '/' . $strImage);

                            $args[$i] = sprintf('<a href="%s" onclick="Backend.openModalImage({\'width\':%s,\'title\':\'%s\',\'url\':\'%s\'});return false"><img src="%s" alt="%s" align="left"></a>',
                                                $strImage, $size[0], str_replace("'", "\\'", $objProduct->name), $strImage,
                                                \Image::get($strImage, 50, 50, 'crop'), $image['alt']);
                            break;
                        }
                    }
                    break;

                case 'name':
                    $args[$i] = $objProduct->name;

                    if ($row['pid'] == 0 && ($objProductType = ProductType::findByPk($row['type'])) !== null && $objProductType->hasVariants()) {
                        // Add a variants link
                        $args[$i] = sprintf('<a href="%s" title="%s">%s</a>', ampersand(\Environment::get('request')) . '&amp;id=' . $row['id'], specialchars($GLOBALS['TL_LANG'][$dc->table]['showVariants']), $args[$i]);
                    }
                    break;

                case 'price':
                    $objPrice = ProductPrice::findPrimaryByProduct($row['id']);

                    if (null !== $objPrice) {
                        $objTax = $objPrice->getRelated('tax_class');
                        $strTax = (null === $objTax ? '' : ' ('.$objTax->getLabel().')');

                        $args[$i] = $objPrice->getValueForTier(1) . $strTax;
                    }
                    break;

                case 'variantFields':
                    $attributes = array();

                    foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
                        $attributes[] = '<strong>' . Isotope::formatLabel($dc->table, $variantField) . ':</strong>&nbsp;' . Isotope::formatValue($dc->table, $variantField, $objProduct->$variantField);
                    }

                    $args[$i] = ($args[$i] ? $args[$i].'<br>' : '') . implode(', ', $attributes);
                    break;
            }
        }

        return $args;
    }
}
