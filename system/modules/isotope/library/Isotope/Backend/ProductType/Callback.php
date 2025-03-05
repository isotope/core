<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Isotope\Backend\Permission;
use Isotope\Haste\Util\Format;
use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Model\Attribute;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


class Callback extends Backend
{

    /**
     * Check permissions to edit table tl_iso_producttype
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('producttypes' !== Input::get('mod')) {
            return;
        }

        $objBackendUser = BackendUser::getInstance();

        if ($objBackendUser->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($objBackendUser->iso_product_types) || \count($objBackendUser->iso_product_types) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $objBackendUser->iso_product_types;
        }

        $GLOBALS['TL_DCA']['tl_iso_producttype']['list']['sorting']['root'] = $root;
    }

    /**
     * Returns all allowed product types as array
     *
     * @return array
     */
    public function getOptions()
    {
        $objUser = BackendUser::getInstance();

        $arrTypes = $objUser->iso_product_types;

        if (!$objUser->isAdmin && (!\is_array($arrTypes) || empty($arrTypes))) {
            $arrTypes = array(0);
        }

        $arrProductTypes = array();
        $objProductTypes = Database::getInstance()->execute('
            SELECT id,name FROM tl_iso_producttype
            WHERE tstamp>0' . ($objUser->isAdmin ? '' : (' AND id IN (' . implode(',', $arrTypes) . ')')) . '
            ORDER BY name
        ');

        while ($objProductTypes->next()) {
            $arrProductTypes[$objProductTypes->id] = $objProductTypes->name;
        }

        return $arrProductTypes;
    }

    /**
     * Make sure at least one variant attribute is enabled
     *
     * @param mixed          $varValue
     *
     * @return mixed
     *
     * @throws \UnderflowException
     * @throws \LogicException
     */
    public function validateVariantAttributes($varValue, DataContainer $dc)
    {
        Controller::loadDataContainer('tl_iso_product');

        $blnError = true;
        $arrAttributes = StringUtil::deserialize($varValue);
        $arrVariantAttributeLabels = array();

        if (!empty($arrAttributes) && \is_array($arrAttributes)) {
            foreach ($arrAttributes as $arrAttribute) {

                /** @var IsotopeAttributeForVariants|Attribute $objAttribute */
                $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$arrAttribute['name']] ?? null;

                if (null !== $objAttribute && /* @todo in 3.0: $objAttribute instanceof IsotopeAttributeForVariants && */$objAttribute->isVariantOption()) {
                    $arrVariantAttributeLabels[] = $objAttribute->name;

                    if ($arrAttribute['enabled']) {
                        $blnError = false;
                    }
                }
            }
        }

        if ($blnError) {
            System::loadLanguageFile('explain');
            throw new \UnderflowException(
                sprintf($GLOBALS['TL_LANG']['tl_iso_producttype']['noVariantAttributes'],
                    implode(', ', $arrVariantAttributeLabels)
                )
            );
        }

        return $varValue;
    }

    /**
     * Check if singular attributes appear in the both product type attributes and variant attributes
     *
     * @param mixed          $value
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    public function validateSingularAttributes($value, DataContainer $dc)
    {
        $productFields  = StringUtil::deserialize($dc->activeRecord->attributes);
        $variantFields  = StringUtil::deserialize($value);
        $singularFields = Attribute::getSingularFields();

        if (!\is_array($productFields) || !\is_array($variantFields) || 0 === \count($singularFields)) {
            return $value;
        }

        $error = [];

        foreach ($singularFields as $singular) {
            foreach ($productFields as $product) {
                if ($product['name'] === $singular) {
                    if ($product['enabled']) {
                        foreach ($variantFields as $variant) {
                            if ($variant['name'] === $singular) {
                                if ($variant['enabled']) {
                                    $error[] = Format::dcaLabel('tl_iso_product', $singular);
                                }

                                break;
                            }
                        }
                    }

                    break;
                }
            }
        }

        if (\count($error) > 0) {
            throw new \LogicException(sprintf(
                $GLOBALS['TL_LANG']['tl_iso_producttype']['singularAttributes'],
                implode(', ', $error)
            ));
        }

        return $value;
    }
}
