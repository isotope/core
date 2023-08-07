<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Model;
use Contao\StringUtil;
use Isotope\Translation;


/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $sorting
 * @property string $name
 * @property string $color
 * @property bool   $paid
 * @property bool   $welcomescreen
 * @property int    $notification
 * @property string $saferpay_status
 */
class OrderStatus extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_orderstatus';

    /**
     * Color style cache
     * @var string
     */
    protected $colorStyles;

    /**
     * Return if the order status means a collection has been paid (configuration flag)
     * @return  bool
     */
    public function isPaid()
    {
        return (bool) $this->paid;
    }

    /**
     * Return the localized order status name
     * @return  string
     */
    public function getName()
    {
        return Translation::get($this->name);
    }

    /**
     * Return the localized order status name
     * Do not use $this->getName(), the alias should not be localized
     * @return  string
     */
    public function getAlias()
    {
        return StringUtil::standardize($this->name);
    }

    /**
     * Generate background and font color for order status color
     * @return  string
     */
    public function getColorStyles()
    {
        if (null === $this->colorStyles) {
            $this->colorStyles = '';

            if ($this->color != '') {
                $this->colorStyles = 'background-color:#' . $this->color;

                $arrRGB = array_map('hexdec', str_split($this->color, 2));
                $hue = 1 - (0.299 * $arrRGB[0] + 0.587 * $arrRGB[1] + 0.114 * $arrRGB[2]) / 255;

                if ($hue > 0.5) {
                    $this->colorStyles .= ';color:#fff';
                }
            }
        }

        return $this->colorStyles;
    }
}
