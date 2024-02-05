<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Contao\ContentDownloads;
use Contao\ContentModel;
use Isotope\Interfaces\IsotopeProduct;

/**
 * Attribute to provide downloads in the product details
 */
class Downloads extends FileTree
{
    /**
     * @inheritdoc
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['fileTree'];
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $objContentModel = new ContentModel();
        $objContentModel->id = 'iso-'.$this->id.'-'.$objProduct->getId();
        $objContentModel->tstamp = time();
        $objContentModel->type = 'downloads';
        $objContentModel->multiSRC = $this->getValue($objProduct);
        $objContentModel->sortBy = $this->sortBy;
        $objContentModel->inline = $this->inline;
        $objContentModel->orderSRC = $objProduct->{$this->field_name.'_order'};
        $objContentModel->cssID = serialize(array('', $this->field_name));

        return (new ContentDownloads($objContentModel))->generate();
    }
}
