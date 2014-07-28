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

namespace Isotope\Model;


/**
 * Class AttributeOption
 *
 * @property int    id
 * @property int    pid
 * @property int    sorting
 * @property int    tstamp
 * @property string ptable
 * @property int    langPid
 * @property string language
 * @property string label
 * @property string type
 * @property bool   published
 */
class AttributeOption extends \MultilingualModel
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_attribute_option';

    /**
     * Get array representation of the attribute option
     *
     * @return array
     */
    public function getAsArray()
    {
        $option = array(
            'value'     => $this->id,
            'label'     => $this->label
        );

        switch ($this->type) {
            case 'blank':
                $option['value'] = '';
                break;

            case 'group':
                $option['group'] = '1';
                break;
        }

        return $option;
    }

    /**
     * Find all options by attribute
     *
     * @param Attribute $objAttribute
     *
     * @return \Model\Collection|null
     */
    public static function findByAttribute(Attribute $objAttribute)
    {
        if ($objAttribute->optionsSource != 'table') {
            throw new \LogicException('Options source for attribute "' . $objAttribute->field_name . '" is not the database table.');
        }

        $t = static::getTable();

        return static::findBy(
            array(
                "$t.pid=?",
                "$t.ptable='tl_iso_attribute'",
                "$t.published='1'"
            ),
            array(
                $objAttribute->id
            ),
            array(
                'order' => "$t.type='blank' DESC, $t.sorting"
            )
        );
    }

    /**
     * Create a Model\Collection object
     *
     * @param array  $arrModels An array of models
     * @param string $strTable  The table name
     *
     * @return \Isotope\Collection\AttributeOption The Model\Collection object
     */
    protected static function createCollection(array $arrModels, $strTable)
    {
        return new \Isotope\Collection\AttributeOption($arrModels, $strTable);
    }


    /**
     * Create a new collection from a database result
     *
     * @param \Database\Result $objResult The database result object
     * @param string           $strTable  The table name
     *
     * @return \Isotope\Collection\AttributeOption The model collection
     */
    protected static function createCollectionFromDbResult(\Database\Result $objResult, $strTable)
    {
        return \Isotope\Collection\AttributeOption::createFromDbResult($objResult, $strTable);
    }
}
