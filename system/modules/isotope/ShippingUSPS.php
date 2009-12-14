<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

class ShippingUSPS extends Shipping
{

	/**
	 * Return an object property
	 *
	 * @access public
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		
		switch( $strKey )
		{
			case 'price':
				$this->import('IsotopeCart', 'Cart');
						
				$arrDestination = array
				(
					'name'			=> $this->Cart->shippingAddress['firstname'] . ' ' . $this->Cart->shippingAddress['lastname'],
					'phone'			=> $this->Cart->shippingAddress['phone'],
					'company'		=> $this->Cart->shippingAddress['company'],
					'street'		=> $this->Cart->shippingAddress['street'],
					'street2'		=> $this->Cart->shippingAddress['street_2'],
					'street3'		=> $this->Cart->shippingAddress['street_3'],
					'city'			=> $this->Cart->shippingAddress['city'],
					'state'			=> $this->Cart->shippingAddress['state'],
					'zip'			=> $this->Cart->shippingAddress['postal'],
					'country'		=> $this->Cart->shippingAddress['country']
				);

				$arrOrigin = array
				(
					'name'			=> $this->Isotope->Store->firstname . ' ' . $this->Isotope->Store->lastname,
					'phone'			=> $this->Isotope->Store->phone,
					'company'		=> $this->Isotope->Store->company,
					'street'		=> $this->Isotope->Store->street,
					'street2'		=> $this->Isotope->Store->street_2,
					'street3'		=> $this->Isotope->Store->street_3,
					'city'			=> $this->Isotope->Store->city,
					'state'			=> $this->Isotope->Store->state,
					'zip'			=> $this->Isotope->Store->postal,
					'country'		=> $this->Isotope->Store->country
				);
				
							
				/*$arrShipment['service'] = ((integer)$this->ups_enabledService < 10 ? "0" . $this->ups_enabledService : $this->ups_enabledService);		//Ground for now
				
				
				$arrShipment['pickup_type']	= array
				(
					'code'			=> '03',		//default to one-time, but needs perhaps to be chosen by store admin.
					'description'	=> '' 
				);
				
				$strWeightUnit = $this->Isotope->Store->weightUnit;
				
				$fltWeight = $this->Cart->totalWeight;*/
				
				
# function USPSParcelRate($weight,$dest_zip) {  
#   
# // This script was written by Mark Sanborn at http://www.marksanborn.net  
# // If this script benefits you are your business please consider a donation  
# // You can donate at http://www.marksanborn.net/donate.    
#   
# // ========== CHANGE THESE VALUES TO MATCH YOUR OWN ===========  
#   
# $userName = 'username'; // Your USPS Username  
# $orig_zip = '12345'; // Zipcode you are shipping FROM  
#   
# // =============== DON'T CHANGE BELOW THIS LINE ===============  
#   
# $url = "http://Production.ShippingAPIs.com/ShippingAPI.dll";  
# $ch = curl_init();  
#   
# // set the target url  
# curl_setopt($ch, CURLOPT_URL,$url);  
# curl_setopt($ch, CURLOPT_HEADER, 1);  
# curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
#   
# // parameters to post  
# curl_setopt($ch, CURLOPT_POST, 1);  
#   
# $data = "API=RateV3&XML=<RateV3Request USERID=\"$userName\"><Package ID=\"1ST\"><Service>PRIORITY</Service><ZipOrigination>$orig_zip</ZipOrigination><ZipDestination>$dest_zip</ZipDestination><Pounds>$weight</Pounds><Ounces>0</Ounces><Size>REGULAR</Size><Machinable>TRUE</Machinable></Package></RateV3Request>";  
#   
# // send the POST values to USPS  
# curl_setopt($ch, CURLOPT_POSTFIELDS,$data);  
#   
# $result=curl_exec ($ch);  
# $data = strstr($result, '<?');  
# // echo '<!-- '. $data. ' -->'; // Uncomment to show XML in comments  
# $xml_parser = xml_parser_create();  
# xml_parse_into_struct($xml_parser, $data, $vals, $index);  
# xml_parser_free($xml_parser);  
# $params = array();  
# $level = array();  
# foreach ($vals as $xml_elem) {  
#     if ($xml_elem['type'] == 'open') {  
#         if (array_key_exists('attributes',$xml_elem)) {  
#             list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);  
#         } else {  
#         $level[$xml_elem['level']] = $xml_elem['tag'];  
#         }  
#     }  
#     if ($xml_elem['type'] == 'complete') {  
#     $start_level = 1;  
#     $php_stmt = '$params';  
#     while($start_level < $xml_elem['level']) {  
#         $php_stmt .= '[$level['.$start_level.']]';  
#         $start_level++;  
#     }  
#     $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';  
#     eval($php_stmt);  
#     }  
# }  
# curl_close($ch);  
# // echo '<pre>'; print_r($params); echo'</pre>'; // Uncomment to see xml tags  
# return $params['RATEV3RESPONSE']['1ST']['1']['RATE'];  
# }  

}

?>