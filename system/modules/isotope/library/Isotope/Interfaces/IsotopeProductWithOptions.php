<?php

namespace Isotope\Interfaces;

interface IsotopeProductWithOptions
{
    /**
     * Return the product's options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Sets the product options.
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Get customer defined field values
     *
     * @return array
     */
    public function getCustomerConfig();

    /**
     * Get variant option field values
     *
     * @return array
     */
    public function getVariantConfig();
}
