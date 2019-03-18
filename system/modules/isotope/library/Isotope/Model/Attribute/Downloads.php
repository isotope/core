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

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to provide downloads in the product details
 *
 * @author Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author Christoph Wiechert <cw@4wardmedia.de>
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
        $objContentModel = new \ContentModel();
        $objContentModel->type = 'downloads';
        $objContentModel->multiSRC = $this->getValue($objProduct);
        $objContentModel->sortBy = $this->sortBy;
        $objContentModel->orderSRC = $objProduct->{$this->field_name.'_order'};
        $objContentModel->cssID = serialize(array('', $this->field_name));

        $objElement = new \ContentDownloads($objContentModel);
        return $objElement->generate();
    }
}
