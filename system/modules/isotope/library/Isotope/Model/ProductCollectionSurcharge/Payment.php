<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollectionSurcharge;

use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Model\Payment as PaymentModel;
use Isotope\Model\ProductCollectionSurcharge;

/**
 * Implements payment surcharge in product collection
 */
class Payment extends ProductCollectionSurcharge implements IsotopeProductCollectionSurcharge
{

    /**
     * Get the source payment model if available
     *
     * @return PaymentModel|null
     */
    public function getSource()
    {
        return PaymentModel::findByPk($this->source_id);
    }
}
