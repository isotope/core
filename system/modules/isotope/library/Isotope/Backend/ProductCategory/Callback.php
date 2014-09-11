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

namespace Isotope\Backend\ProductCategory;

use Isotope\Model\Product;


class Callback extends \Backend
{

    /**
     * List the products
     * @param array
     * @return string
     */
    public function listRows($row)
    {
        $objProduct = Product::findByPk($row['pid']);

		$arrImages = deserialize($objProduct->images);
		$strImageHtml = '';

		if (is_array($arrImages) && !empty($arrImages)) {

			$strImage = 'isotope/' . strtolower(substr($arrImages[0]['src'], 0, 1)) . '/' . $arrImages[0]['src'];

			if (is_file(TL_ROOT . '/' . $strImage)) {

				$size = @getimagesize(TL_ROOT . '/' . $strImage);

				$strImageHtml = sprintf('<a href="%s" target="_blank" onclick="Backend.openModalImage({\'width\':%s,\'title\':\'%s\',\'url\':\'%s\'});return false"><img src="%s" alt="%s" align="left"></a>',
					$strImage, $size[0], str_replace("'", "\\'", $objProduct->name), $strImage,
					\Image::get($strImage, 50, 50, 'crop'), $arrImages[0]['alt']);
			}
		}

		return $strImageHtml . '<span style="display: block;float: left;margin: 0 0 0 10px;padding: 18px 0;">' . $objProduct->name . '</span>';
	}


    /**
     * Return the page view button
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function getPageViewButton($href, $label, $title, $class, $attributes, $table, $root)
    {
        $objPage = \PageModel::findWithDetails(\Input::get('page_id'));

        if (null === $objPage) {
            return '';
        }

        return '<a href="contao/main.php?do=feRedirect&page=' . $objPage->id . '" target="_blank" class="header_preview" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a>';
    }
}
