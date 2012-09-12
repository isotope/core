<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class PasteProductButton extends Backend
{
	/**
	 * True if group already added
	 * @var bool
	 */
	static private $blnHasGroup = false;

	/**
	 * Handle the paste button callback for tl_iso_products
	 * @param DataContainer
	 * @param array
	 * @param string
	 * @param bool
	 * @param array
	 * @return string
	 * @link http://www.contao.org/callbacks.html#paste_button_callback
	 */
	public function generate(DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
	{
		// Disable all buttons if there is a circular reference
		if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || ($table == $dc->table && $arrClipboard['id'] == $row['id'])) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || ($table == $dc->table && in_array($row['id'], $arrClipboard['id'])))))
		{
			return '';
		}

		// make sure there's at least one product group
		if (!self::$blnHasGroup)
		{
			if (IsotopeBackend::createGeneralGroup())
			{
				$this->reload();
			}

			self::$blnHasGroup = true;
		}

		// Can't do anything in root
		if ($row['id'] == 0)
		{
			return '';
		}

		// Create a new product or variant
		if ($arrClipboard['mode'] == 'create')
		{
			return $this->Input->get('type') == 'variant' ? $this->createVariant($table, $row, $arrClipboard) : $this->createProduct($table, $row, $arrClipboard);
		}

		// Copy or cut a single product or variant
		if ($arrClipboard['mode'] == 'cut' || $arrClipboard['mode'] == 'copy')
		{
			$objProduct = $this->Database->prepare("SELECT p.*, t.variants FROM tl_iso_products p LEFT JOIN tl_iso_producttypes t ON p.type=t.id WHERE p.id=?")->execute($arrClipboard['id']);

			// Variant
			if ($objProduct->pid > 0)
			{
				return $this->pasteVariant($objProduct, $table, $row, $arrClipboard);
			}
			else
			{
				return $this->pasteProduct($objProduct, $table, $row, $arrClipboard);
			}
		}

		// Cut or copy multiple products. Cannot be variants because the checkbox is not available.
		elseif ($arrClipboard['mode'] == 'cutAll' || $arrClipboard['mode'] == 'copyAll')
		{
			$objProduct = $this->Database->prepare("SELECT p.*, t.variants FROM tl_iso_products p LEFT JOIN tl_iso_producttypes t ON p.type=t.id WHERE p.id=?")->execute($arrClipboard['id']);

			return $this->pasteAll($objProduct, $table, $row, $arrClipboard);
		}

		$this->Session->set('CLIPBOARD', null);
		throw new Exception('Unhandled paste_button_callback mode "' . $arrClipboard['mode'] . '"');
	}


	/**
	 * Return paste button for new product
	 * @return string
	 */
	protected function createProduct($table, $row, $arrClipboard)
	{
		// Can't create product in product
		if ($table == 'tl_iso_products' && $row['id'] > 0)
		{
			return '';
		}

		return $this->getPasteButton(true, $this->addToUrl('act=create&amp;mode=2&amp;gid='.$row['id']), $table, $row['id']);
	}


	/**
	 * Return paste button for new variant
	 * @return string
	 */
	protected function createVariant($table, $row, $arrClipboard)
	{
		// Can't create variant in product group or root node or variant
		if ($table == 'tl_iso_groups' || $row['id'] == 0 || $row['pid'] > 0)
		{
			return '';
		}

		// Disable paste button for products without variant data
		elseif ($table == 'tl_iso_products' && $row['id'] > 0)
		{
			$objType = $this->Database->prepare("SELECT * FROM tl_iso_producttypes WHERE id=?")->execute($row['type']);

			if (!$objType->variants)
			{
				return $this->getPasteButton(false);
			}
		}

		return $this->getPasteButton(true, $this->addToUrl('act=create&amp;mode=2&amp;pid='.$row['id']), $table, $row['id']);
	}


	/**
	 * Copy or paste a single product
	 * @return string
	 */
	protected function pasteProduct(Database_Result $objProduct, $table, $row, $arrClipboard)
	{
		// Can't paste product in product or variant
		if ($table == 'tl_iso_products' && $row['id'] > 0)
		{
			return '';
		}

		return $this->getPasteButton(true, $this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;gid='.$row['id']), $table, $row['id']);
	}


	/**
	 * Copy or paste a single variant
	 * @return string
	 */
	protected function pasteVariant(Database_Result $objProduct, $table, $row, $arrClipboard)
	{
		// Can't paste variant in product group or root node or variant
		if ($table == 'tl_iso_groups' || $row['id'] == 0 || $row['pid'] > 0)
		{
			return '';
		}

		// Can't copy variant into it's current product
		elseif ($table == 'tl_iso_products' && $objProduct->pid == $row['id'] && $arrClipboard['mode'] == 'copy')
		{
			return $this->getPasteButton(false);
		}

		// Disable paste button for products without variant data
		elseif ($table == 'tl_iso_products' && $row['id'] > 0)
		{
			$objType = $this->Database->prepare("SELECT * FROM tl_iso_producttypes WHERE id=?")->execute($row['type']);

			if (!$objType->variants)
			{
				return $this->getPasteButton(false);
			}
		}

		return $this->getPasteButton(true, $this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id']), $table, $row['id']);
	}


	/**
	 * Copy or paste multiple products
	 * @return string
	 */
	protected function pasteAll($objProduct, $table, $row, $arrClipboard)
	{
		// Can't paste products in product or variant
		if ($table == 'tl_iso_products' && $row['id'] > 0)
		{
			return '';
		}

		return $this->getPasteButton(true, $this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;childs=1&amp;gid='.$row['id']), $table, $row['id']);
	}


	/**
	 * Return the paste button image
	 * @param bool
	 * @param string
	 * @param string
	 * @return string
	 */
	protected function getPasteButton($blnActive, $url='#', $table='', $id='')
	{
		if (!$blnActive)
		{
			return $this->generateImage('pasteinto_.gif', '', 'class="blink"');
		}

		$strImage = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $id), 'class="blink"');

		return '<a href="'.$url.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$strImage.'</a> ';
	}
}

