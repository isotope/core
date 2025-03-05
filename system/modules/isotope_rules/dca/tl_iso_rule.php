<?php

/**
 * Load tl_iso_product data container
 */
\Contao\System::loadLanguageFile(\Isotope\Model\Product::getTable());
\Contao\Controller::loadDataContainer(\Isotope\Model\Product::getTable());

$GLOBALS['TL_DCA']['tl_iso_rule'] = array
(
    'config' => array
    (
        'dataContainer' => \Contao\DC_Table::class,
        'ctable'                            => array('tl_iso_rule_restriction'),
        'enableVersioning'                  => false,
        'onload_callback' => array
        (
            array('\Isotope\Backend\Rule\Callback', 'loadAttributeValues'),
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
            )
        ),
    ),
    'list' => array
    (
        'sorting' => array
        (
            'mode'                          => 1,
            'panelLayout'                   => 'filter;search,limit',
            'fields'                        => array('type', 'name'),
        ),
        'label'      => array
        (
            'fields'                        => array('name', 'code'),
            'format'                         => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
        ),
    ),
    'palettes' => array
    (
        '__selector__'                      => array('type', 'applyTo', 'enableCode', 'configRestrictions', 'memberRestrictions', 'productRestrictions'),
        'default'                           => '{basic_legend},type,name',
        'product'                           => '{basic_legend},type,name,discount,rounding;{limit_legend:hide},limitPerMember,limitPerConfig,minItemQuantity,maxItemQuantity,quantityMode;{datim_legend:hide},startDate,endDate,startTime,endTime;{advanced_legend:hide},configRestrictions,memberRestrictions,productRestrictions;{enabled_legend},enabled',
        'cart'                              => '{basic_legend},type,applyTo,name,label,discount,rounding;{coupon_legend:hide},enableCode;{limit_legend:hide},limitPerMember,limitPerConfig,minSubtotal,maxSubtotal,minWeight,maxWeight,minItemQuantity,maxItemQuantity,quantityMode;{datim_legend:hide},startDate,endDate,startTime,endTime;{advanced_legend:hide},configRestrictions,memberRestrictions,productRestrictions;{enabled_legend},enabled,groupOnly',
        'cartsubtotal'                      => '{basic_legend},type,applyTo,name,label,discount,tax_class,rounding;{coupon_legend:hide},enableCode;{limit_legend:hide},limitPerMember,limitPerConfig,minSubtotal,maxSubtotal,minWeight,maxWeight,minItemQuantity,maxItemQuantity,quantityMode;{datim_legend:hide},startDate,endDate,startTime,endTime;{advanced_legend:hide},configRestrictions,memberRestrictions,productRestrictions;{enabled_legend},enabled,groupOnly',
        'cart_group'                        => '{basic_legend},type,name;{group_legend},groupRules,groupCondition;{coupon_legend:hide},enableCode;{limit_legend:hide},limitPerMember,limitPerConfig,minSubtotal,maxSubtotal,minWeight,maxWeight,minItemQuantity,maxItemQuantity,quantityMode;{datim_legend:hide},startDate,endDate,startTime,endTime;{advanced_legend:hide},configRestrictions,memberRestrictions,productRestrictions;{enabled_legend},enabled,groupOnly',
    ),
    'subpalettes' => array
    (
        'enableCode'                        => 'code,singleCode',
        'configRestrictions'                => 'configs,configCondition',
        'memberRestrictions_guests'         => 'memberCondition',
        'memberRestrictions_groups'         => 'memberCondition,groups',
        'memberRestrictions_members'        => 'memberCondition,members',
        'productRestrictions_producttypes'  => 'productCondition,producttypes',
        'productRestrictions_pages'         => 'productCondition,pages',
        'productRestrictions_products'      => 'productCondition,products',
        'productRestrictions_variants'      => 'productCondition,variants',
        'productRestrictions_attribute'     => 'attributeName,attributeCondition,attributeValue',
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                           => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array
        (
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'type' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['type'],
            'filter'                        => true,
            'default'                       => 'product',
            'inputType'                     => 'select',
            'options'                       => array('product', 'cart', 'cart_group'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['type'],
            'eval'                          => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                           => "varchar(32) NOT NULL default ''",
        ),
        'name' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['name'],
            'search'                        => true,
            'inputType'                     => 'text',
            'eval'                          => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr w50'),
            'sql'                           => "varchar(255) NOT NULL default ''",
        ),
        'label' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['label'],
            'search'                        => true,
            'inputType'                     => 'text',
            'eval'                          => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                           => "varchar(255) NOT NULL default ''",
        ),
        'discount' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['discount'],
            'search'                        => true,
            'inputType'                     => 'text',
            'eval'                          => array('mandatory'=>true, 'maxlength'=>16, 'rgxp'=>'discount', 'tl_class'=>'clr w50'),
            'sql'                           => "varchar(16) NOT NULL default ''",
        ),
        'tax_class' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['tax_class'],
            'filter'                        => true,
            'inputType'                     => 'select',
            'foreignKey'                    => \Isotope\Model\TaxClass::getTable().'.name',
            'options_callback'              => array('\Isotope\Model\TaxClass', 'getOptionsWithSplit'),
            'eval'                          => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                           => "int(10) NOT NULL default '0'",
            'relation'                      => array('type'=>'hasOne', 'load'=>'lazy'),
        ),
        'groupRules' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['groupRules'],
            'inputType'                     => 'checkboxWizard',
            'foreignKey'                    => 'tl_iso_rule.name',
            'options_callback'              => static function ($dc) {
                return \Contao\Database::getInstance()
                    ->prepare("SELECT id, name FROM tl_iso_rule WHERE (type='cart' OR type='cart_group') AND id!=?")
                    ->execute($dc->id)
                    ->fetchEach('name');
            },
            'eval'                          => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50'),
            'sql'                           => "blob NULL",
            'relation'                      => array('type'=>'hasMany', 'load'=>'lazy'),
        ),
        'groupCondition' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['groupCondition'],
            'inputType'                     => 'radio',
            'options'                       => array(\Isotope\Model\Rule::GROUP_FIRST, \Isotope\Model\Rule::GROUP_ALL),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['groupCondition'],
            'eval'                          => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                           => "varchar(8) NOT NULL default 'first'",
        ),
        'applyTo' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo'],
            'default'                       => 'products',
            'inputType'                     => 'select',
            'options'                       => array('products', 'items', 'subtotal'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['applyTo'],
            'eval'                          => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                           => "varchar(8) NOT NULL default ''",
        ),
        'rounding' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['rounding'],
            'inputType'                     => 'radio',
            'options'                       => array('normal', 'down', 'up'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['rounding'],
            'eval'                          => array('mandatory'=>true, 'tl_class'=>'w50 w50h'),
            'sql'                           => "varchar(8) NOT NULL default ''",
        ),
        'enableCode' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['enableCode'],
            'filter'                        => true,
            'inputType'                     => 'checkbox',
            'eval'                          => array('submitOnChange'=>true),
            'sql'                           => "char(1) NOT NULL default ''",
        ),
        'code' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['code'],
            'search'                        => true,
            'inputType'                     => 'text',
            'eval'                          => array('mandatory'=>true, 'maxlength'=>255),
            'sql'                           => "varchar(255) NOT NULL default ''",
        ),
        'singleCode' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['singleCode'],
            'inputType'                     => 'checkbox',
            'sql'                           => "char(1) NOT NULL default ''",
        ),
        'limitPerMember' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerMember'],
            'inputType'                     => 'text',
            'eval'                          => array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50'),
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'limitPerConfig' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['limitPerConfig'],
            'inputType'                     => 'text',
            'eval'                          => array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50'),
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'minSubtotal' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['minSubtotal'],
            'inputType'                     => 'text',
            'eval'                          => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'maxSubtotal' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['maxSubtotal'],
            'inputType'                     => 'text',
            'eval'                          => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'minWeight' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['minWeight'],
            'default'                       => array('unit'=>'kg'),
            'inputType'                     => 'timePeriod',
            'options'                       => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'                     => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                          => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                           => "varchar(255) NOT NULL default ''",
        ),
        'maxWeight' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['maxWeight'],
            'default'                       => array('unit'=>'kg'),
            'inputType'                     => 'timePeriod',
            'options'                       => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
            'reference'                     => &$GLOBALS['TL_LANG']['WGT'],
            'eval'                          => array('rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                           => "varchar(255) NOT NULL default ''",
        ),
        'minItemQuantity' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['minItemQuantity'],
            'inputType'                     => 'text',
            'eval'                          => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'maxItemQuantity' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['maxItemQuantity'],
            'inputType'                     => 'text',
            'eval'                          => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                           => "int(10) unsigned NOT NULL default '0'",
        ),
        'quantityMode' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode'],
            'inputType'                     => 'select',
            'default'                       => 'product_quantity',
            'options'                       => array('product_quantity', 'cart_products', 'cart_items'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['quantityMode'],
            'eval'                          => array('tl_class'=>'w50'),
            'sql'                           => "varchar(32) NOT NULL default ''",
        ),
        'startDate' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['startDate'],
            'inputType'                     => 'text',
            'eval'                          => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                           => "varchar(10) NOT NULL default ''",
        ),
        'endDate' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['endDate'],
            'inputType'                     => 'text',
            'eval'                          => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                           => "varchar(10) NOT NULL default ''",
        ),
        'startTime' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['startTime'],
            'inputType'                     => 'text',
            'eval'                          => array('rgxp'=>'time', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                           => "varchar(10) NOT NULL default ''",
        ),
        'endTime' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['endTime'],
            'inputType'                     => 'text',
            'eval'                          => array('rgxp'=>'time', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                           => "varchar(10) NOT NULL default ''",
        ),
        'configRestrictions' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['configRestrictions'],
            'inputType'                     => 'checkbox',
            'filter'                        => true,
            'eval'                          => array('submitOnChange'=>true, 'tl_class'=>'clr'),
            'sql'                           => "char(1) NOT NULL default ''",
        ),
        'configCondition' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['configCondition'],
            'default'                       => '1',
            'inputType'                     => 'radio',
            'options'                       => array('1'=>&$GLOBALS['TL_LANG']['tl_iso_rule']['condition_true'], '0'=>&$GLOBALS['TL_LANG']['tl_iso_rule']['condition_false']),
            'eval'                          => array('isAssociative'=>true, 'tl_class'=>'w50'),
            'sql'                           => "tinyint(1) NOT NULL default '0'",
        ),
        'configs' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['configs'],
            'inputType'                     => 'checkbox',
            'foreignKey'                    => \Isotope\Model\Config::getTable().'.name',
            'eval'                          => array('mandatory'=>true, 'multiple'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'clr w50 w50h'),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'memberRestrictions' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions'],
            'inputType'                     => 'radio',
            'default'                       => 'none',
            'filter'                        => true,
            'options'                       => array('none', 'guests', 'groups', 'members'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions'],
            'eval'                          => array('submitOnChange'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                           => "varchar(32) NOT NULL default ''",
        ),
        'memberCondition' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['memberCondition'],
            'default'                       => '1',
            'inputType'                     => 'radio',
            'options'                       => array('1'=>$GLOBALS['TL_LANG']['tl_iso_rule']['condition_true'], '0'=>$GLOBALS['TL_LANG']['tl_iso_rule']['condition_false']),
            'eval'                          => array('isAssociative'=>true, 'tl_class'=>'w50'),
            'sql'                           => "tinyint(1) NOT NULL default '0'",
        ),
        'groups' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['groups'],
            'inputType'                     => 'checkbox',
            'foreignKey'                    => 'tl_member_group.name',
            'eval'                          => array('mandatory'=>true, 'multiple'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'clr'),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'members' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['members'],
            'inputType'                     => 'tableLookup',
            'eval' => array
            (
                'mandatory'                 => true,
                'doNotSaveEmpty'            => true,
                'tl_class'                  => 'clr',
                'foreignTable'              => 'tl_member',
                'fieldType'                 => 'checkbox',
                'listFields'                => array('firstname', 'lastname', 'username', 'email'),
                'searchFields'              => array('firstname', 'lastname', 'username', 'email'),
                'sqlWhere'                  => '',
                'searchLabel'               => 'Search members',
            ),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'productRestrictions' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions'],
            'inputType'                     => 'radio',
            'default'                       => 'none',
            'filter'                        => true,
            'options'                       => array('none', 'producttypes', 'pages', 'products', 'variants', 'attribute'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions'],
            'eval'                          => array('submitOnChange'=>true, 'tl_class'=>'clr w50 w50h'),
            'sql'                           => "varchar(32) NOT NULL default ''",
        ),
        'productCondition' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['productCondition'],
            'default'                       => '1',
            'inputType'                     => 'radio',
            'options'                       => array('1'=>$GLOBALS['TL_LANG']['tl_iso_rule']['condition_true'], '0'=>$GLOBALS['TL_LANG']['tl_iso_rule']['condition_false']),
            'eval'                          => array('isAssociative'=>true, 'tl_class'=>'w50'),
            'sql'                           => "tinyint(1) NOT NULL default '0'",
        ),
        'producttypes' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['producttypes'],
            'inputType'                     => 'checkbox',
            'foreignKey'                    => \Isotope\Model\ProductType::getTable().'.name',
            'eval'                          => array('mandatory'=>true, 'multiple'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'clr'),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'pages' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['pages'],
            'inputType'                     => 'pageTree',
            'foreignKey'                    => 'tl_page.title',
            'eval'                          => array('mandatory'=>true, 'multiple'=>true, 'fieldType'=>'checkbox', 'doNotSaveEmpty'=>true, 'tl_class'=>'clr'),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'products'     => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['products'],
            'inputType'                     => 'tableLookup',
            'eval' => array
            (
                'mandatory'                 => true,
                'doNotSaveEmpty'            => true,
                'tl_class'                  => 'clr',
                'foreignTable'              => 'tl_iso_product',
                'fieldType'                 => 'checkbox',
                'listFields'                => array(\Isotope\Model\ProductType::getTable().'.name', 'name', 'sku'),
                'joins'                     => array
                (
                    \Isotope\Model\ProductType::getTable() => array
                    (
                        'type' => 'LEFT JOIN',
                        'jkey' => 'id',
                        'fkey' => 'type',
                    ),
                ),
                'searchFields'              => array('name', 'alias', 'sku', 'description'),
                'customLabels'              => array
                (
                    $GLOBALS['TL_DCA'][\Isotope\Model\Product::getTable()]['fields']['type']['label'][0],
                    $GLOBALS['TL_DCA'][\Isotope\Model\Product::getTable()]['fields']['name']['label'][0],
                    $GLOBALS['TL_DCA'][\Isotope\Model\Product::getTable()]['fields']['sku']['label'][0],
                ),
                'sqlWhere'                  => 'pid=0',
                'searchLabel'               => 'Search products',
            ),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'variants' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['variants'],
            'inputType'                     => 'text',
            'eval'                          => array('mandatory'=>true, 'doNotSaveEmpty'=>true, 'csv'=>',', 'tl_class'=>'clr long'),
            'load_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'loadRestrictions'),
            ),
            'save_callback' => array
            (
                array('\Isotope\Backend\Rule\Callback', 'saveRestrictions'),
            ),
        ),
        'attributeName' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['attributeName'],
            'inputType'                     => 'select',
            'options_callback'              => array('\Isotope\Backend\Rule\Callback', 'getAttributeNames'),
            'eval'                          => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr w50'),
            'sql'                           => "varchar(32) NOT NULL default ''",
        ),
        'attributeCondition' => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition'],
            'inputType'                     => 'select',
            'options'                       => array('eq', 'neq', 'lt', 'gt', 'elt', 'egt', 'starts', 'ends', 'contains'),
            'reference'                     => &$GLOBALS['TL_LANG']['tl_iso_rule']['attributeCondition'],
            'eval'                          => array('mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                           => "varchar(8) NOT NULL default ''",
        ),
        'attributeValue' => array
        (
            'eval'                          => array('decodeEntities'=>true, 'tl_class'=>'clr'),
            'sql'                           => "varchar(255) NOT NULL default ''",
        ),
        'enabled'    => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['enabled'],
            'toggle'                        => true,
            'inputType'                     => 'checkbox',
            'filter'                        => true,
            'eval'                          => array('doNotCopy'=>true, 'tl_class'=>'w50'),
            'sql'                           => "char(1) NOT NULL default ''",
        ),
        'groupOnly'    => array
        (
            'label'                         => &$GLOBALS['TL_LANG']['tl_iso_rule']['groupOnly'],
            'inputType'                     => 'checkbox',
            'filter'                        => true,
            'eval'                          => array('tl_class'=>'w50'),
            'sql'                           => "char(1) NOT NULL default ''",
        ),
    )
);
