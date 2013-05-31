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
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
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

            self::$objInstance->arrProductTypes = array();
            $blnDownloads = false;
            $blnVariants = false;
            $blnAdvancedPrices = false;

            $objProductTypes = self::$objInstance->Database->query("SELECT t.id, t.variants, t.downloads, t.prices, t.attributes, t.variant_attributes FROM tl_iso_products p LEFT JOIN tl_iso_producttypes t ON p.type=t.id GROUP BY p.type");

            while ($objProductTypes->next())
            {
                self::$objInstance->arrProductTypes[$objProductTypes->id] = $objProductTypes->row();
                self::$objInstance->arrProductTypes[$objProductTypes->id]['attributes'] = deserialize($objProductTypes->attributes, true);
                self::$objInstance->arrProductTypes[$objProductTypes->id]['variant_attributes'] = deserialize($objProductTypes->variant_attributes, true);

                if ($objProductTypes->downloads)
                {
                    $blnDownloads = true;
                }

                if ($objProductTypes->variants)
                {
                    $blnVariants = true;
                }

                if ($objProductTypes->prices)
                {
                    $blnAdvancedPrices = true;
                }
            }

            // If no downloads are enabled in any product type, we do not need the option
            if (!$blnDownloads)
            {
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['downloads']);
            }
            else
            {
                // Cache number of downloads
                self::$objInstance->arrDownloads = array();

                $objDownloads = self::$objInstance->Database->query("SELECT pid, COUNT(id) AS total FROM tl_iso_downloads GROUP BY pid");

                while ($objDownloads->next())
                {
                    self::$objInstance->arrDownloads[$objDownloads->pid] = $objDownloads->total;
                }
            }

            // Disable all variant related operations
            if (!$blnVariants)
            {
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['toggleVariants']);
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['quick_edit']);
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['generate']);
            }

            // Disable prices button if not enabled in any product type
            if (!$blnAdvancedPrices)
            {
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['prices']);
            }

            // Disable related categories if none are defined
            if (self::$objInstance->Database->query("SELECT COUNT(id) AS total FROM tl_iso_related_categories")->total == 0)
            {
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['related']);
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
		$session = $this->Session->getData();

		// Store filter values in the session
		foreach ($_POST as $k=>$v)
		{
			if (substr($k, 0, 4) != 'iso_')
			{
				continue;
			}

			// Reset the filter
			if ($k == \Input::post($k))
			{
				unset($session['filter']['tl_iso_products'][$k]);
			}
			// Apply the filter
			else
			{
				$session['filter']['tl_iso_products'][$k] = \Input::post($k);
			}
		}

		$this->Session->setData($session);
		$arrProducts = null;

		// Filter the products
		foreach ($session['filter']['tl_iso_products'] as $k=>$v)
		{
			if (substr($k, 0, 4) != 'iso_')
			{
				continue;
			}

			switch ($k)
			{
				// Show products with or without images
				case 'iso_noimages':
                    $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE pid=0 AND language='' AND images " . ($v ? " IS NULL" : " IS NOT NULL"));
                    $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
					break;

				// Show products with or without category
                case 'iso_nocategory':
                    $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products p WHERE pid=0 AND language='' AND (SELECT COUNT(*) FROM tl_iso_product_categories c WHERE c.pid=p.id)" . ($v ? "=0" : ">0"));
                    $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                    break;

				// Show new products
                case 'iso_new':
                	$date = 0;

					switch ($v)
					{
						case 'new_today':
							$date = strtotime('-1 day');
							break;

						case 'new_week':
							$date = strtotime('-1 week');
							break;

						case 'new_month':
							$date = strtotime('-1 month');
							break;
					}

                    $objProducts = $this->Database->execute("SELECT id FROM tl_iso_products p WHERE pid=0 AND language='' AND dateAdded>=".$date);
                    $arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $objProducts->fetchEach('id')) : $objProducts->fetchEach('id');
                    break;

                default:
                    // !HOOK: add custom advanced filters
                    if (isset($GLOBALS['ISO_HOOKS']['applyAdvancedFilters']) && is_array($GLOBALS['ISO_HOOKS']['applyAdvancedFilters']))
                    {
                        foreach ($GLOBALS['ISO_HOOKS']['applyAdvancedFilters'] as $callback)
                        {
                            $objCallback = \System::importStatic($callback[0]);
                            $arrReturn = $objCallback->$callback[1]($k);

                            if (is_array($arrReturn))
                            {
								$arrProducts = is_array($arrProducts) ? array_intersect($arrProducts, $arrReturn) : $arrReturn;
                                break;
                            }
                        }
                    }

                    \System::log('Advanced product filter "' . $k . '" not found.', __METHOD__, TL_ERROR);
                    break;
			}
		}

		if (is_array($arrProducts) && empty($arrProducts))
		{
			$arrProducts = array(0);
		}

		$GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = $arrProducts;
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

        $arrProducts = \Isotope\Backend::getAllowedProductIds();

        // Method will return true if no limits should be applied (e.g. user is admin)
        if (true === $arrProducts)
        {
            return;
        }

        // Filter by product type and group permissions
        if (empty($arrProducts))
        {
            unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_variant']);
            unset($session['CLIPBOARD']['tl_iso_products']);
            $session['CURRENT']['IDS'] = array();
            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['filter'][] = array('id=?', 0);

            if (false === $arrProducts)
            {
                unset($GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['new_product']);
            }
        }
        else
        {
            // Maybe another function has already set allowed product IDs
            if (is_array($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root']))
            {
                $arrProducts = array_intersect($GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'], $arrProducts);
            }

            $GLOBALS['TL_DCA']['tl_iso_products']['list']['sorting']['root'] = $arrProducts;

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
                \System::log('Cannot access product ID '.\Input::get('id'), __METHOD__, TL_ERROR);
                \Controller::redirect('contao/main.php?act=error');
            }
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
                $arrFields['prices']['exclude'] = $arrFields['price']['exclude'];
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
                    $arrInherit[$attribute] = Isotope::formatLabel('tl_iso_products', $attribute);
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
            \Controller::reload();
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
        if (\Input::get('act') !== 'create')
        {
            return;
        }

        if (($intProductTypeId = \Isotope\Backend::getProductTypeForGroup($this->Session->get('iso_products_gid'))) !== false)
        {
            $GLOBALS['TL_DCA']['tl_iso_products']['fields']['type']['default'] = $intProductTypeId;
        }
    }


    /**
     * Add a script that will handle "move all" action
     */
    public function addMoveAllFeature()
    {
	    if (\Input::get('act') == 'select' && !\Input::get('id'))
	    {
		    $GLOBALS['TL_MOOTOOLS'][] = "
<script>
window.addEvent('domready', function() {
  $('cut').addEvents({
    'click': function(e) {
      e.preventDefault();
      Isotope.openModalGroupSelector({'width':765,'title':'".specialchars($GLOBALS['TL_LANG']['MSC']['groupPicker'])."','url':'system/modules/isotope/public/group.php?do=".\Input::get('do')."&amp;table=tl_iso_groups&amp;field=gid&amp;value=".$this->Session->get('iso_products_gid')."','action':'moveProducts','trigger':$(this)});
    },
    'closeModal': function() {
      var form = $('tl_select'),
          hidden = new Element('input', { type:'hidden', name:'cut' }).inject(form.getElement('.tl_formbody'), 'top');
      form.submit();
    }
  });
});
</script>";
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
    //  !panel_callback
    //////////////////////


    /**
     * Generate product filter buttons and return them as HTML
     * @return string
     */
    public function generateFilterButtons()
    {
	    return '
<div class="tl_filter iso_filter tl_subpanel">
<input type="button" id="groupFilter" class="tl_submit" onclick="Backend.getScrollOffset();Isotope.openModalGroupSelector({\'width\':765,\'title\':\''.specialchars($GLOBALS['TL_LANG']['MSC']['groupPicker']).'\',\'url\':\'system/modules/isotope/public/group.php?do='.\Input::get('do').'&amp;table=tl_iso_groups&amp;field=gid&amp;value='.$this->Session->get('iso_products_gid').'\',\'action\':\'filterGroups\'});return false" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['filterByGroups']).'">
</div>';
    }


    /**
     * Generate advanced filter panel and return them as HTML
     * @return string
     */
    public function generateAdvancedFilters()
    {
    	$session = $this->Session->getData();

		// Filters
		$arrFilters = array
		(
			'iso_noimages' => array
			(
				'name'    => 'iso_noimages',
				'label'   => $GLOBALS['TL_LANG']['tl_iso_products']['filter_noimages'],
				'options' => array(''=>$GLOBALS['TL_LANG']['MSC']['no'], 1=>$GLOBALS['TL_LANG']['MSC']['yes'])
			),
			'iso_nocategory' => array
			(
				'name'    => 'iso_nocategory',
				'label'   => $GLOBALS['TL_LANG']['tl_iso_products']['filter_nocategory'],
				'options' => array(''=>$GLOBALS['TL_LANG']['MSC']['no'], 1=>$GLOBALS['TL_LANG']['MSC']['yes'])
			),
			'iso_new' => array
			(
				'name'    => 'iso_new',
				'label'   => $GLOBALS['TL_LANG']['tl_iso_products']['filter_new'],
				'options' => array('new_today'=>$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_today'], 'new_week'=>$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_week'], 'new_month'=>$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_month'])
			)
		);

	    $strBuffer = '
<div class="tl_filter iso_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['tl_iso_products']['filter'] . '</strong>' . "\n";

		// Generate filters
		foreach ($arrFilters as $arrFilter)
		{
			$blnActive = false;
			$strOptions = '
  <option value="' . $arrFilter['name'] . '">' . $arrFilter['label'] . '</option>
  <option value="' . $arrFilter['name'] . '">---</option>' . "\n";

			// Generate options
			foreach ($arrFilter['options'] as $k=>$v)
			{
				$selected = '';

				// Check if the option is active
				if ($session['filter']['tl_iso_products'][$arrFilter['name']] === (string) $k)
				{
					$blnActive = true;
					$selected = ' selected';
				}

				$strOptions .= '  <option value="' . $k . '"' . $selected . '>' . $v . '</option>' . "\n";
			}

			$strBuffer .= '<select name="' . $arrFilter['name'] . '" id="' . $arrFilter['name'] . '" class="tl_select' . ($blnActive ? ' active' : '') . '">
' . $strOptions . '
</select>' . "\n";
		}

		return $strBuffer . '</div>';
    }


    //////////////////////
    //  !label_callback
    //////////////////////


    /**
     * Generate a product label and return it as HTML string
	 * @param array
	 * @param string
	 * @param object
	 * @param array
	 * @return string
     */
    public function getRowLabel($row, $label, $dc, $args)
    {
        $arrImages = deserialize($row['images']);
        $args[0] = '&nbsp;';

        // Add an image
        if (is_array($arrImages) && !empty($arrImages))
        {
            foreach ($arrImages as $image)
            {
                $strImage = 'isotope/' . strtolower(substr($image['src'], 0, 1)) . '/' . $image['src'];

                if (!is_file(TL_ROOT . '/' . $strImage))
                {
                    continue;
                }

				$size = @getimagesize(TL_ROOT . '/' . $strImage);

                $args[0] = sprintf('<a href="%s" onclick="Backend.openModalImage({\'width\':%s,\'title\':\'%s\',\'url\':\'%s\'});return false"><img src="%s" alt="%s" align="left"></a>',
                					$strImage, $size[0], str_replace("'", "\\'", $row['name']), $strImage,
                					$this->getImage($strImage, 50, 50, 'crop'), $image['alt']);
                break;
            }
        }

        // Add a variants link
        if (!$row['pid'])
        {
        	$args[1] = sprintf('<a href="%s" title="%s">%s</a>', ampersand(\Environment::get('request')) . '&amp;id=' . $row['id'], specialchars($GLOBALS['TL_LANG']['tl_iso_products']['showVariants']), $row['name']);
        }

        return $args;
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
            $href = ampersand(str_replace('&'.$href, '', \Environment::get('request')));
        }
        else
        {
            $href = ampersand(\Environment::get('request') . '&') . $href;
        }

        return ' &#160; :: &#160; <a href="'.$href.'" class="'.$class.'" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
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
     */
    public function filterRemoveButton($href, $label, $title, $class, $attributes, $table, $root)
    {
        $href = preg_replace('/&?filter\[\]=[^&]*/', '', \Environment::get('request'));

        return ' &#160; :: &#160; <a href="'.$href.'" class="header_iso_filter_remove" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
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
        if (!$this->User->isAdmin && (!is_array($this->User->iso_groupp) || empty($this->User->iso_groupp)))
        {
            return '';
        }

        return '<a href="' . $this->addToUrl('&amp;' . $href) . '" class="header_icon" title="' . specialchars($title) . '"' . $attributes . '>' . specialchars($label) . '</a>';
    }



    ///////////////////////////////////////////
    //  !button_callback (operations)
    ///////////////////////////////////////////


    /**
     * Hide generate and quick edit button for variants and product types without variant support     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function variantsButton($row, $href, $label, $title, $icon, $attributes)
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
        if ($row['pid'] > 0)
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

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['downloads']['label'][2], (int) $this->arrDownloads[$row['id']]) . $title).'"'.$attributes.'>'.$this->generateImage($icon, $label) .'</a> ';
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
        $objUser = \BackendUser::getInstance();
        $arrTypes = $objUser->iso_product_types;

        if (!$objUser->isAdmin && (!is_array($arrTypes) || empty($arrTypes)))
        {
            $arrTypes = array(0);
        }

        $arrProductTypes = array();
        $objProductTypes = $this->Database->execute("SELECT id,name FROM tl_iso_producttypes WHERE tstamp>0" . ($objUser->isAdmin ? '' : (" AND id IN (" . implode(',', $arrTypes) . ")")) . " ORDER BY name");

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
