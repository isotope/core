<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductAction;

use Contao\System;
use Isotope\Interfaces\IsotopeProduct;

class LegacyButtonAction extends AbstractButton
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $classes;

    /**
     * @var array|null
     */
    private $callback;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $label
     */
    public function __construct($name, $label, array $callback = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProduct $product = null)
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProduct $product, array $config = [])
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getClasses(IsotopeProduct $product)
    {
        return $this->classes;
    }

    /**
     * @param mixed $classes
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProduct $product, array $config = [])
    {
        if (!isset($_POST[$this->name])) {
            return false;
        }

        if (null !== $this->callback) {
            $objCallback = System::importStatic($this->callback[0]);
            $objCallback->{$this->callback[1]}($product, $config);
        }

        return true;
    }
}
