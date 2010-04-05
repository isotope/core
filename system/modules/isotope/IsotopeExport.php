<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
class IsotopeExport extends Backend
{
	public function exportOrderEmails(DataContainer $dc)
	{
		if ($this->Input->get('key') != 'export_emails')
		{
			return '';
		}
		
		$objOrders = $this->Database->execute("SELECT billing_address FROM tl_iso_orders");
		
		if(!$objOrders->numRows)
		{			    		
			echo 'No orders found.';
		
		}

		while($objOrders->next())
		{
			$arrBillingData = deserialize($objOrders->billing_address);
			
			if($arrBillingData['email'])
			{
				$arrExport[] = $arrBillingData['firstname'] . ' ' . $arrBillingData['lastname'] . ' <' . $arrBillingData['email'] . '>';
			}
		}

		if(count($arrExport))
		{
		header('Content-Type: application/csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="isotope_order_emails_export_' . time() .'.csv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		$output = '';
		
		foreach ($arrExport as $export) 
		{
			$output .= '"' . $export . '"' . "\n";
		}

		echo $output;
		exit;
		}
		
		return;

	}

}