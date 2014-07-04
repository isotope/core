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

namespace Isotope\Backend\Product;

use Haste\Util\Format;
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
                    $args[$i]  = '&nbsp;';

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

                    /** @var \Isotope\Model\ProductType $objProductType */
                    if ($row['pid'] == 0 && ($objProductType = ProductType::findByPk($row['type'])) !== null && $objProductType->hasVariants()) {
                        // Add a variants link
                        $args[$i] = sprintf('<a href="%s" title="%s">%s</a>', ampersand(\Environment::get('request')) . '&amp;id=' . $row['id'], specialchars($GLOBALS['TL_LANG'][$dc->table]['showVariants']), $args[$i]);
                    }
                    break;

                case 'price':
                    $objPrice = ProductPrice::findPrimaryByProductId($row['id']);

                    if (null !== $objPrice) {
                        /** @var \Isotope\Model\TaxClass $objTax */
                        $objTax = $objPrice->getRelated('tax_class');
                        $strTax = (null === $objTax ? '' : ' (' . $objTax->getLabel() . ')');

                        $args[$i] = $objPrice->getValueForTier(1) . $strTax;
                    }
                    break;

                case 'variantFields':
                    $attributes = array();

                    foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
                        $attributes[] = '<strong>' . Format::dcaLabel($dc->table, $variantField) . ':</strong>&nbsp;' . Format::dcaValue($dc->table, $variantField, $objProduct->$variantField);
                    }

                    $args[$i] = ($args[$i] ? $args[$i] . '<br>' : '') . implode(', ', $attributes);
                    break;
            }
        }

        return $args;
    }
}
