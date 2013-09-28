<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */

namespace Isotope;


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once '../../initialize.php';


/**
 * Class ProductGroupPicker
 *
 * Back end Isotope group picker.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */
class ProductGroupPicker extends \Backend
{

    /**
     * Current Ajax object
     * @var object
     */
    protected $objAjax;


    /**
     * Initialize the controller
     *
     * 1. Import the user
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        $this->import('BackendUser', 'User');
        parent::__construct();

        $this->User->authenticate();
        \System::loadLanguageFile('default');
    }


    /**
     * Run the controller and parse the template
     */
    public function run()
    {
        $this->Template = new \BackendTemplate('be_picker');
        $this->Template->main = '';

        // Ajax request
        if ($_POST && \Environment::get('isAjaxRequest')) {
            $this->objAjax = new \Ajax(\Input::post('action'));
            $this->objAjax->executePreActions();
        }

        $strTable = \Input::get('table');
        $strField = \Input::get('field');

        // Define the current ID
        define('CURRENT_ID', \Input::get('id'));

        $this->loadDataContainer($strTable);
        \System::loadLanguageFile($strTable);
        $objDca = new \DC_Table($strTable);

        // AJAX request
        if ($_POST && \Environment::get('isAjaxRequest')) {
            $this->objAjax->executePostActions($objDca);
        }

        $this->Session->set('groupPickerRef', \Environment::get('request'));

        // Prepare the widget
        $objProductGroupTree = new $GLOBALS['BE_FFL']['productGroupSelector'](array(
            'strId'    => $strField,
            'strTable' => $strTable,
            'strField' => $strField,
            'strName'  => $strField,
            'varValue' => explode(',', \Input::get('value'))
        ), $objDca);

        $this->Template->main = $objProductGroupTree->generate();
        $this->Template->theme = \Backend::getTheme();
        $this->Template->base = \Environment::get('base');
        $this->Template->language = $GLOBALS['TL_LANGUAGE'];
        $this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['filepicker']);
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['ppHeadline'];
        $this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->Template->options = $this->createPageList();
        $this->Template->expandNode = $GLOBALS['TL_LANG']['MSC']['expandNode'];
        $this->Template->collapseNode = $GLOBALS['TL_LANG']['MSC']['collapseNode'];
        $this->Template->loadingData = $GLOBALS['TL_LANG']['MSC']['loadingData'];
        $this->Template->search = $GLOBALS['TL_LANG']['MSC']['search'];
        $this->Template->action = ampersand(\Environment::get('request'));
        $this->Template->value = $this->Session->get('product_group_selector_search');
        $this->Template->addSearch = true;
        $this->Template->breadcrumb = \Isotope\Backend::generateGroupsBreadcrumb($this->Session->get('iso_products_gid'));

        if ($this->User->isAdmin || (is_array($this->User->iso_groupp) && !empty($this->User->iso_groupp))) {
            $this->Template->manager = $GLOBALS['TL_LANG']['tl_iso_groups']['manager'];
            $this->Template->managerHref = 'contao/main.php?do=iso_products&amp;table=tl_iso_groups&amp;popup=1';
        }

        $GLOBALS['TL_CONFIG']['debugMode'] = false;
        $this->Template->output();
    }
}


/**
 * Instantiate the controller
 */
$objFilePicker = new ProductGroupPicker();
$objFilePicker->run();
