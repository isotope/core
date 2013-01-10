<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;


/**
 * Class ProductCallbacks
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class ProductCallbacks extends \Backend
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance;

    /**
     * paste_button_callback Provider
     * @var mixed
     */
    protected $PasteProductButton;

    /**
     * Product type cache
     * @var array
     */
    protected $arrProductTypes;

    /**
     * Cache if there are related categories
     * @var bool
     */
    protected $blnHasRelated;


    /**
     * Cache number of downloads per product
     * @var array
     */
    protected $arrDownloads;


    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone() {}


    /**
     * Import a back end user and Isotope objects
     */
    protected function __construct()
    {
        parent::__construct();

        $this->import('BackendUser', 'User');
        $this->import('Isotope\Isotope', 'Isotope');
    }


    /**
     * Instantiate the Isotope object
     * @return object
     */
    public static function getInstance()
    {
        if (!is_object(self::$objInstance))
        {
            self::$objInstance = new \Isotope\ProductCallbacks();


            // Cache product types
            self::$objInstance->arrProductTypes = array();

            $objProductTypes = self::$objInstance->Database->query("SELECT t.id, t.variants, t.downloads, t.prices, t.attributes, t.variant_attributes FROM tl_iso_products p LEFT JOIN tl_iso_producttypes t ON p.type=t.id GROUP BY p.type");

            while ($objProductTypes->next())
            {
                self::$objInstance->arrProductTypes[$objProductTypes->id] = $objProductTypes->row();
                self::$objInstance->arrProductTypes[$objProductTypes->id]['attributes'] = deserialize($objProductTypes->attributes, true);
                self::$objInstance->arrProductTypes[$objProductTypes->id]['variant_attributes'] = deserialize($objProductTypes->variant_attributes, true);
            }


            // Cache if tehre are categories
            self::$objInstance->blnHasRelated = (self::$objInstance->Database->query("SELECT COUNT(id) AS total FROM tl_iso_related_categories")->total > 0);


            // Cache number of downloads
            self::$objInstance->arrDownloads = array();

            $objDownloads = self::$objInstance->Database->query("SELECT pid, COUNT(id) AS total FROM tl_iso_downloads GROUP BY pid");

            while ($objDownloads->next())
            {
                self::$objInstance->arrDownloads[$objDownloads->pid] = $objDownloads->total;
            }
        }

        return self::$objInstance;
    }



    ///////////////////////
    //  !onload_callback
    ///////////////////////


    /**
     * Apply advanced filters to product list view
     * @return void
     */
    public function applyAdvancedFilters()
    {
        $arrFilters = \Input::get('filter');

        if (\Input::get('act') == '' && \Input::get('key') == '' && is_array($arrFilters))
        {
            $arrProducts = null;
            $arrNames = array();

            foreach ($arrFilters as $filter)
            {
                switch ($filter)
                {
                    case 'noimages':
                        $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE pid=0 AND language='' AND images IS NULL");
                        $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                        break;

                    case 'nocategory':
                        $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products p WHERE pid=0 AND language='' AND (SELECT COUNT(*) FROM tl_iso_product_categories c WHERE c.pid=p.id)=0");
                        $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                        break;

                    case 'new_today':
                        $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products p WHERE pid=0 AND language='' AND dateAdded>=".strtotime('-1 day'));
                        $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                        break;

                    case 'new_week':
                        $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products p WHERE pid=0 AND language='' AND dateAdded>=".strtotime('-1 week'));
                        $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                        break;

                    case 'new_month':
                        $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products p WHERE pid=0 AND language='' AND dateAdded>=".strtotime('-1 month'));
                        $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                        break;

                    default:
                        // !HOOK: add custom advanced filters
                        if (isset($GLOBALS['ISO_HOOKS']['applyAdvancedFilters']) && is_array($GLOBALS['ISO_HOOKS']['applyAdvancedFilters']))
                        {
                            foreach ($GLOBALS['ISO_HOOKS']['applyAdvancedFilters'] as $callback)
                            {
                                $objCallback = (in_array('getInstance', get_class_methods($callback[0]))) ? call_user_func(array($callback[0], 'getInstance')) : new $callback[0]();
                                $arrReturn = $objCallback->$callback[1]($filter);

                                if (is_array($arrReturn))
                                {
                                    $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $arrReturn) : $arrReturn;
                                    break;
                                }
                            }
                        }

                        $this->log('Advanced product filter "'.$filter.'" not found.', __METHOD__, TL_ERROR);
                        break;
                }

                $arrNames[] = $GLOBALS['TL_LANG']['tl_iso_products']['filter_'.$filter][0];
            }

            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = $arrProducts;
            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['breadcrumb'] .= '<p class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_products']['filter'][0] . ': ' . implode(', ', $arrNames) . '</p><br>';
        }
    }


    /**
     * Check permissions for that entry
     * @return void
     */
    public function checkPermission()
    {
        if (\Input::get('act') != '' && (\Input::get('mode') == '' || is_numeric(\Input::get('mode'))))
        {
            $GLOBALS['TL_DCA']['tl_iso_products']['config']['closed'] = false;
        }

        // Hide "add variant" button if no products with variants enabled exist
        if ($this->Database->query("SELECT COUNT(*) AS total FROM tl_iso_products p LEFT JOIN tl_iso_producttypes t ON p.type=t.id WHERE t.variants='1'")->total == 0)
        {
            unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_variant']);
        }

        $session = $this->Session->getData();

        if ($this->User->isAdmin)
        {
            return;
        }

        // Filter by product type and group permissions
        if (!is_array($this->User->iso_product_types) || empty($this->User->iso_product_types) || !is_array($this->User->iso_groups) || empty($this->User->iso_groups))
        {
            $GLOBALS['TL_DCA']['tl_iso_products']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_product']);
            unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_variant']);
            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = array();
        }
        else
        {
            $arrGroups = array_merge($this->User->iso_groups, $this->getChildRecords($this->User->iso_groups, 'tl_iso_groups'));

            $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE type IN (0," . implode(',', $this->User->iso_product_types) . ") AND gid IN (" . implode(',', $arrGroups) . ") AND pid=0 AND language=''");
            $arrProducts = $objProducts->numRows ? $objProducts->fetchEach('id') : array();

            // Maybe another function has already set allowed product IDs
            if (is_array($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root']))
            {
                $arrProducts = array_intersect($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'], $arrProducts);
            }

            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = $arrProducts;
        }

        // Need to fetch all variant IDs because they are editable too
        if (!empty($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root']))
        {
            $arrVariants = $this->getChildRecords($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'], 'tl_iso_products');
            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = array_merge($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'], $arrVariants);
        }

        // Set allowed product IDs (edit multiple)
        if (is_array($session['CURRENT']['IDS']))
        {
            $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root']);
        }

        // Set allowed clipboard IDs
        if (is_array($session['CLIPBOARD']['tl_iso_products']['id']))
        {
            $session['CLIPBOARD']['tl_iso_products']['id'] = array_intersect($session['CLIPBOARD']['tl_iso_products']['id'], $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'], $this->Database->query("SELECT id FROM tl_iso_products WHERE pid=0")->fetchEach('id'));

            if (empty($session['CLIPBOARD']['tl_iso_products']['id']))
            {
                unset($session['CLIPBOARD']['tl_iso_products']);
            }
        }

        // Overwrite session
        $this->Session->setData($session);

        if (\Input::get('id') > 0 && !in_array(\Input::get('id'), $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root']))
        {
            $this->log('Cannot access product ID '.\Input::get('id'), __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
    }


    /**
     * Build palette for the current product type/variant
     * @param object
     * @return void
     */
    public function buildPaletteString($dc)
    {
        $this->import('Isotope\Isotope', 'Isotope');
        $this->loadDataContainer('tl_iso_attributes');

        if (\Input::get('act') == '' && \Input::get('key') == '' || \Input::get('act') == 'select')
        {
            return;
        }

        $arrFields = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];
        $arrLegendSort = array_merge(array('variant_legend'), $GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['legend']['options']);

        // Set default product type
        $arrFields['type']['default'] = (int) $this->Database->execute("SELECT id FROM tl_iso_producttypes WHERE fallback='1'" . ($this->User->isAdmin ? '' : (" AND id IN (" . implode(',', $this->User->iso_product_types) . ")")))->id;

        // Set default tax class
        $arrFields['tax_class']['default'] = (int) $this->Database->execute("SELECT id FROM tl_iso_tax_class WHERE fallback='1'")->id;

        $blnEditAll = true;

        $strQuery = "SELECT
                        id,
                        pid,
                        language,
                        type,
                        (SELECT type FROM tl_iso_products p2 WHERE p2.id=p1.pid) AS parent_type,
                        (SELECT attributes FROM tl_iso_producttypes WHERE id=p1.type) AS attributes,
                        (SELECT variant_attributes FROM tl_iso_producttypes WHERE id=p1.type) AS variant_attributes,
                        (SELECT prices FROM tl_iso_producttypes WHERE id=p1.type) AS prices
                    FROM tl_iso_products p1";


        if (\Input::get('act') != 'editAll' && $dc->id > 0)
        {
            $strQuery .= ' WHERE id=' . $dc->id;
            $blnEditAll = false;
        }

        $objProducts = $this->Database->execute($strQuery);
        $blnReload = false;

        while ($objProducts->next())
        {
            if ($objProducts->pid > 0 && $objProducts->parent_type != '' && $objProducts->type != $objProducts->parent_type)
            {
                $this->Database->query("UPDATE tl_iso_products SET type={$objProducts->parent_type} WHERE id={$objProducts->id}");
                $blnReload = true;
            }

            if ($blnReload)
            {
                continue;
            }

            // Enable advanced prices
            if ($objProducts->prices && !$blnEditAll)
            {
                $arrFields['prices']['attributes'] = $arrFields['price']['attributes'];
                $arrFields['price'] = $arrFields['prices'];
            }

            $arrInherit = array();
            $arrPalette = array();

            $objProducts->attributes = deserialize($objProducts->attributes, true);

            // Variant
            if ($objProducts->pid > 0)
            {
                $arrPalette['variant_legend'][] = 'variant_attributes' . ($blnEditAll ? '' : ',inherit');

                // @todo will not work in edit all, should use option_callback!
                foreach ($objProducts->attributes as $attribute => $arrConfig)
                {
                    if ($arrConfig['enabled'] && $arrFields[$attribute]['attributes']['variant_option'])
                    {
                        $arrFields['variant_attributes']['options'][] = $attribute;
                    }
                }

                $arrAttributes = deserialize($objProducts->variant_attributes, true);
            }
            else
            {
                $arrAttributes = $objProducts->attributes;
            }

            foreach ($arrAttributes as $attribute => $arrConfig)
            {
                // Field is disabled or not an attribute
                if (!$arrConfig['enabled'] || !is_array($arrFields[$attribute]) || $arrFields[$attribute]['attributes']['legend'] == '')
                {
                    continue;
                }

                // Do not show variant options & customer defined fields
                if ($arrFields[$attribute]['attributes']['variant_option'] || $arrFields[$attribute]['attributes']['customer_defined'] || $GLOBLAS['ISO_ATTR'][$arrFields[$attribute]['attributes']['type']]['customer_defined'])
                {
                    continue;
                }

                // Field cannot be edited in variant
                if ($objProducts->pid > 0 && $arrFields[$attribute]['attributes']['inherit'])
                {
                    continue;
                }

                $arrPalette[$arrFields[$attribute]['attributes']['legend']][$arrConfig['position']] = $attribute;

                // Apply product type attribute config
                if (($tl_class = trim($arrConfig['tl_class_select'] . ' ' . $arrConfig['tl_class_text'])) != '')
                {
                    $arrFields[$attribute]['eval']['tl_class'] = $tl_class;
                }

                if ($arrConfig['mandatory'] > 0)
                {
                    $arrFields[$attribute]['eval']['mandatory'] = $arrConfig['mandatory'] == 1 ? false : true;
                }

                if (!$blnEditAll && !in_array($attribute, array('sku', 'price', 'shipping_weight', 'published')) && $objProducts->attributes[$attribute]['enabled'])
                {
                    $arrInherit[$attribute] = $this->Isotope->formatLabel('tl_iso_products', $attribute);
                }
            }

            $arrLegends = array();

            // Build
            foreach ($arrPalette as $legend=>$fields)
            {
                ksort($fields);
                $arrLegends[array_search($legend, $arrLegendSort)] = '{' . $legend . '},' . implode(',', $fields);
            }

            ksort($arrLegends);

            // Set inherit options
            $arrFields['inherit']['options'] = $arrInherit;

            // Add palettes
            $GLOBALS['TL_DCA']['tl_iso_products']['palettes'][$objProducts->type . $objProducts->pid] = implode(';', $arrLegends);
        }

        if ($blnReload)
        {
            $this->reload();
        }
        elseif ($blnEditAll)
        {
            $arrFields['inherit']['exclude'] = true;
            $arrFields['prices']['exclude'] = true;
            $arrFields['variant_attributes']['exclude'] = true;
        }
    }


    /**
     * Load the default product type
     * @param object
     * @return void
     */
    public function loadDefaultProductType($dc)
    {
        if ($this->Input->get('act') !== 'create' || !$this->Input->get('gid'))
        {
            return;
        }

        if (($intProductTypeId = \Isotope\Backend::getProductTypeForGroup($this->Input->get('gid'))) !== false)
        {
            $GLOBALS['TL_DCA']['tl_iso_products']['fields']['type']['default'] = $intProductTypeId;
        }
    }




    ///////////////////////
    //  !oncopy_callback
    ///////////////////////


    /**
     * Update sorting of product in categories when duplicating, move new product to the bottom
     * @param integer
     * @param object
     * @link http://www.contao.org/callbacks.html#oncopy_callback
     */
    public function updateCategorySorting($insertId, $dc)
    {
        $objCategories = $this->Database->query("SELECT c1.*, MAX(c2.sorting) AS max_sorting FROM tl_iso_product_categories c1 LEFT JOIN tl_iso_product_categories c2 ON c1.page_id=c2.page_id WHERE c1.pid=" . (int) $insertId . " GROUP BY c1.page_id");

        while ($objCategories->next())
        {
            $this->Database->query("UPDATE tl_iso_product_categories SET sorting=" . ($objCategories->max_sorting + 128) . " WHERE id=" . $objCategories->id);
        }
    }



    /////////////////////////
    //  !onsubmit_callback
    /////////////////////////


    /**
     * Store the date when the product has been added
     * @param DataContainer
     * @return void
     */
    public function storeDateAdded(\DataContainer $dc)
    {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord || $dc->activeRecord->dateAdded > 0)
        {
            return;
        }

        $this->Database->prepare("UPDATE tl_iso_products SET dateAdded=? WHERE id=?")
                       ->execute(time(), $dc->id);
    }



    //////////////////////
    //  !label_callback
    //////////////////////


    /**
     * Generate a product label and return it as HTML string
     * @param array
     * @param string
     * @return string
     */
    public function getRowLabel($row, $label = '')
    {
        $arrImages = deserialize($row['images']);
        $thumbnail = '&nbsp;';

        if (is_array($arrImages) && !empty($arrImages))
        {
            foreach ($arrImages as $image)
            {
                $strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];

                if (!is_file(TL_ROOT . '/' . $strImage))
                {
                    continue;
                }

                $thumbnail = sprintf('<img src="%s" alt="%s" align="left">', $this->getImage($strImage, 34, 34, 'proportional'), $image['alt']);
                break;
            }
        }

        $objProductType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id=". (int) $row['type']);
        $arrAttributes = deserialize($objProductType->attributes, true);

        if ($row['pid'] > 0)
        {
            $strBuffer = '<div class="iso_product"><div class="thumbnail">'.$thumbnail.'</div><ul>';

            foreach ($arrAttributes as $attribute => $arrConfig)
            {
                if ($arrConfig['enabled'] && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
                {
                    $strBuffer .= '<li><strong>' . $this->Isotope->formatLabel('tl_iso_products', $attribute) . ':</strong> ' . $this->Isotope->formatValue('tl_iso_products', $attribute, $row[$attribute]) . '</li>';
                }
            }

            return $strBuffer . '</ul></div>';
        }

        return '<div class="iso_product"><div class="thumbnail">'.$thumbnail.'</div><p>' . $row['name'] . (($row['sku'] != '' && $arrAttributes['sku']['enabled']) ? '<span style="color:#b3b3b3; padding-left:3px;">['.$row['sku'].']</span>' : '') . '</p><div>' . ($row['pid']==0 ? '<em>' . $this->getCategoryList($row['id']) . '</em>' : '') . '</div></div> ';
    }



    ///////////////////////////////////////////
    //  !button_callback (global_operations)
    ///////////////////////////////////////////


    /**
     * Return the filter button, allow for multiple filters
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     * @todo remove "isotope-filter" static class when Contao Defect #3504 has been implemented
     */
    public function filterButton($href, $label, $title, $class, $attributes, $table, $root)
    {
        static $arrFilters = false;

        if ($arrFilters === false)
        {
            $arrFilters = (array) \Input::get('filter');
        }

        $filter = str_replace('filter[]=', '', $href);

        if (in_array($filter, $arrFilters))
        {
            $href = ampersand(str_replace('&'.$href, '', $this->Environment->request));
        }
        else
        {
            $href = ampersand($this->Environment->request . '&') . $href;
        }

        return ' &#160; :: &#160; <a href="'.$href.'" class="'.$class.' isotope-filter" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
    }


    /**
     * Return the "remove filter" button (unset url parameters)
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     * @todo remove static classes when Contao Defect #3504 has been implemented
     */
    public function filterRemoveButton($href, $label, $title, $class, $attributes, $table, $root)
    {
        $href = preg_replace('/&?filter\[\]=[^&]*/', '', $this->Environment->request);
        return ' &#160; :: &#160; <a href="'.$href.'" class="header_iso_filter_remove isotope-filter" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
    }


    /**
     * Hide "toggle all groups" button if there are no groups at all
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function toggleGroups($href, $label, $title, $class, $attributes, $table, $root)
    {
        $objGroups = $this->Database->query("SELECT COUNT(id) AS hasGroups FROM tl_iso_groups");

        if (!$objGroups->hasGroups)
        {
            return '';
        }

        return '<a href="' . $this->addToUrl('&amp;' . $href) . '" class="header_toggle isotope-tools" title="' . specialchars($title) . '"' . $attributes . '>' . specialchars($label) . '</a>';
    }


    /**
     * Hide "toggle all variants" button if there are no variants at all
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function toggleVariants($href, $label, $title, $class, $attributes, $table, $root)
    {
        $objVariants = $this->Database->query("SELECT COUNT(id) AS hasVariants FROM tl_iso_products WHERE pid>0 AND language=''");

        if (!$objVariants->hasVariants)
        {
            return '';
        }

        return '<a href="' . $this->addToUrl('&amp;' . $href) . '" class="header_toggle isotope-tools" title="' . specialchars($title) . '"' . $attributes . '>' . specialchars($label) . '</a>';
    }


    /**
     * Hide "product groups" button for non-admins
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param array
     * @return string
     */
    public function groupsButton($href, $label, $title, $class, $attributes, $table, $root)
    {
        if (!$this->User->isAdmin && (!is_array($this->User->iso_groupp) || empty($this->User->iso_groupp) || !is_array($this->User->iso_groups) || empty($this->User->iso_groups)))
        {
            return '';
        }

        return '<a href="' . $this->addToUrl('&amp;' . $href) . '" class="header_iso_groups isotope-tools" title="' . specialchars($title) . '"' . $attributes . '>' . specialchars($label) . '</a>';
    }



    ///////////////////////////////////////////
    //  !button_callback (operations)
    ///////////////////////////////////////////


    /**
     * Hide generate button for variants and product types without variant support
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function quickEditButton($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0 || !$this->arrProductTypes[$row['type']]['variants'])
        {
            return '';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }


    /**
     * Hide generate button for variants and product types without variant support
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function generateButton($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0 || !$this->arrProductTypes[$row['type']]['variants'])
        {
            return '';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }


    /**
     * Hide "related" button for variants
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function relatedButton($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['pid'] > 0 || !$this->blnHasRelated)
        {
            return '';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }


    /**
     * Show/hide the downloads button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function downloadsButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!$this->arrProductTypes[$row['type']]['downloads'])
        {
            return '';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).' '. (int) $this->arrDownloads[$row['id']] .'</a> ';
    }


    /**
     * Show/hide the prices button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function pricesButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!$this->arrProductTypes[$row['type']]['prices'])
        {
            return '';
        }

        $arrAttributes = $this->arrProductTypes[$row['type']][($row['pid'] > 0 ? 'variant_attributes' : 'attributes')];

        if (!$arrAttributes['price']['enabled'])
        {
            return '';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }



    ////////////////////////
    //  !options_callback
    ////////////////////////


    /**
     * Returns all allowed product types as array
     * @param DataContainer
     * @return array
     */
    public function getProductTypes(\DataContainer $dc)
    {
        $this->import('BackendUser', 'User');
        $arrTypes = $this->User->iso_product_types;

        if (!$this->User->isAdmin && (!is_array($arrTypes) || empty($arrTypes)))
        {
            $arrTypes = array(0);
        }

        $arrProductTypes = array();
        $objProductTypes = $this->Database->execute("SELECT id,name FROM tl_iso_producttypes WHERE tstamp>0" . ($this->User->isAdmin ? '' : (" AND id IN (" . implode(',', $arrTypes) . ")")) . " ORDER BY name");

        while ($objProductTypes->next())
        {
            $arrProductTypes[$objProductTypes->id] = $objProductTypes->name;
        }

        return $arrProductTypes;
    }



    /////////////////////
    //  !load_callback
    /////////////////////


    /**
     * Load page IDs from tl_iso_product_categories table
     * @param mixed
     * @param DataContainer
     * @return mixed
     */
    public function loadProductCategories($varValue, \DataContainer $dc)
    {
        return $this->Database->execute("SELECT page_id FROM tl_iso_product_categories WHERE pid={$dc->id}")->fetchEach('page_id');
    }



    /////////////////////
    //  !save_callback
    /////////////////////


    /**
     * Save page ids to tl_iso_product_categories table. This allows to retrieve all products associated to a page.
     * @param mixed
     * @param DataContainer
     * @return mixed
     */
    public function saveProductCategories($varValue, \DataContainer $dc)
    {
        $arrIds = deserialize($varValue);

        if (is_array($arrIds) && !empty($arrIds))
        {
            $time = time();
            $this->Database->query("DELETE FROM tl_iso_product_categories WHERE pid={$dc->id} AND page_id NOT IN (" . implode(',', $arrIds) . ")");
            $objPages = $this->Database->execute("SELECT page_id FROM tl_iso_product_categories WHERE pid={$dc->id}");
            $arrIds = array_diff($arrIds, $objPages->fetchEach('page_id'));

            foreach ($arrIds as $id)
            {
                $sorting = $this->Database->executeUncached("SELECT MAX(sorting) AS sorting FROM tl_iso_product_categories WHERE page_id=$id")->sorting + 128;
                $this->Database->query("INSERT INTO tl_iso_product_categories (pid,tstamp,page_id,sorting) VALUES ({$dc->id}, $time, $id, $sorting)");
            }
        }
        else
        {
            $this->Database->query("DELETE FROM tl_iso_product_categories WHERE pid={$dc->id}");
        }

        return $varValue;
    }


    /**
     * Autogenerate a product alias if it has not been set yet
     * @param mixed
     * @param DataContainer
     * @return string
     * @throws Exception
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $varValue = standardize(\Input::post('name'));

            if ($varValue == '')
            {
                $varValue = standardize(\Input::post('sku'));
            }

            if ($varValue == '')
            {
                $varValue = strlen($dc->activeRecord->name) ? standardize($dc->activeRecord->name) : standardize($dc->activeRecord->sku);
            }

            if ($varValue == '')
            {
                $varValue = $dc->id;
            }
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE id=? OR alias=?")
                                   ->execute($dc->id, $varValue);

        // Check whether the product alias exists
        if ($objAlias->numRows > 1)
        {
            if (!$autoAlias)
            {
                throw new OverflowException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '.' . $dc->id;
        }

        return $varValue;
    }


    ///////////////
    //  !HELPERS
    ///////////////


    /**
     * Produce a list of categories for the backend listing
     * @param integer
     * @return string
     */
    protected function getCategoryList($intProduct)
    {
        static $arrCategoriesByProduct;
        static $arrCategories = array();

        if ($arrCategoriesByProduct === null)
        {
            $arrCategoriesByProduct = array();
            $objCategories = $this->Database->query("SELECT pid, GROUP_CONCAT(page_id) AS categories FROM tl_iso_product_categories GROUP BY pid");

            while ($objCategories->next())
            {
                $arrCategoriesByProduct[$objCategories->pid] = explode(',', $objCategories->categories);
            }
        }

        $arrResult = array();

        foreach ((array) $arrCategoriesByProduct[$intProduct] as $intPage)
        {
            if (!isset($arrPageDetails[$intPage]))
            {
                $arrHelp = array();
                $pid = $intPage;

                do
                {
                    $objParent = $this->Database->execute("SELECT id, pid, title, type FROM tl_page WHERE id=" . $pid);

                    if (!$objParent->numRows)
                        break;

                    $pid = $objParent->pid;
                    $arrHelp[] = $objParent->title;
                }
                while ($pid > 0 && $objParent->type != 'root');

                $arrCategories[$intPage] = empty($arrHelp) ? false : '<a class="tl_tip" longdesc="' . implode(' » ', array_reverse($arrHelp)) . '" href="contao/main.php?do=iso_products&table=tl_iso_product_categories&id=' . $intPage . '">' . reset($arrHelp) . '</a>';
            }

            if ($arrCategories[$intPage] === false)
                continue;

            $arrResult[] = $arrCategories[$intPage];
        }

        if (empty($arrResult))
        {
            return $GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'];
        }

        return $GLOBALS['TL_LANG']['tl_iso_products']['pages'][0] . ': ' . implode(', ', $arrResult);
    }
}
