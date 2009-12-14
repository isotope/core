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
 

class ShippingUPS extends Shipping
{
	/**
	 * Node name for the Monetary Value
	 * 
	 * @var string
	 */
	const NODE_NAME_MONETARY_VALUE = 'MonetaryValue';
	
	/**
	 * Node name for the Rated Shipment Node
	 * 
	 * @var string
	 */
	const NODE_NAME_RATED_SHIPMENT = 'RatedShipment';
	
	/**
	 * Node name for the root node
	 * 
	 * @var string
	 */
	const NODE_NAME_ROOT_NODE = 'RatingServiceSelectionResponse';
	
		/**
	 * Destination (ship to) data
	 * 
	 * Should be in the format:
	 * $destination = array(
	 * 	'name' => '',
	 * 	'attn' => '',
	 * 	'phone' => '1234567890',
	 * 	'address' => array(
	 * 		'street1' => '',
	 * 		'street2' => '',
	 * 		'city' => '',
	 * 		'state' => '**',
	 * 		'zip' => 12345,
	 * 		'country' => '',
	 * 	),
	 * );
	 * 
	 * @access protected
	 * @var array
	 */
	protected $destination = array();
	
	/**
	 * Shipment data
	 * 
	 * @access protected
	 * @var array
	 */
	protected $shipment = array();
	
	/**
	 * Ship from data
	 * 
	 * @access protected
	 * @var array
	 */
	protected $ship_from = array();
	
	/**
	 * Shipper data
	 * 
	 * @access protected
	 * @var array
	 */
	protected $shipper = array();

	/**
	 *
	 */
	protected $intStatusFail = 0;
	
	/** 
	 * 
	 */
	protected $intStatusPass = 1;
	

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
				
							
				$arrShipment['service'] = ((integer)$this->ups_enabledService < 10 ? "0" . $this->ups_enabledService : $this->ups_enabledService);		//Ground for now
				
				
				$arrShipment['pickup_type']	= array
				(
					'code'			=> '03',		//default to one-time, but needs perhaps to be chosen by store admin.
					'description'	=> '' 
				);
				
				$strWeightUnit = $this->Isotope->Store->weightUnit;
				
				$fltWeight = $this->Cart->totalWeight;
					
				$arrShipment['packages'][] = array
				(					
					'packaging'		=> array
					(
						'code'			=> '02',	//counter
						'description'	=> ''
					),
					'description'	=> '',
					'units'			=> $strWeightUnit,   //weight unit code, lbs or kgs.
					'weight'		=> $fltWeight,	//shipment weight...  product field "weight" * "quantity_requested" 
					
				);								
							
				// set object properties
				$this->server = 'https://www.ups.com/ups.app/xml/Rate';
				
				$this->shipment = $arrShipment;  
				
				$this->shipper = $arrOrigin;	//FOR NOW, This is assumed to be the same for origin and shipping info			
				$this->ship_from = $arrOrigin;  //FOR NOW, This is assumed to be the same for origin and shipping info.  Could be used to ship from multiple fulfillment places, drop shipping, etc.
				
				$this->destination = $arrDestination;
										
				return $this->getShippingRate();
					

				break;
		}
		
		return parent::__get($strKey);
	}
	
	protected function getShippingRate()
	{
		$strRequestXML = $this->buildRequest('RatingServiceSelectionRequest');
								
		$strResponseXML = $this->sendRequest($strRequestXML);
	
		//Get price for service.
		$this->response = new DOMDocument();
		$this->response->loadXML($strResponseXML);
		
		$this->xpath = new DOMXPath($this->response);
		
		$domNode = $this->xpath->query(
			'/RatingServiceSelectionResponse/RatedShipment/RatedPackage/TotalCharges/MonetaryValue')->item(0);
				
		return floatval($domNode->nodeValue);
		
	}
	
	protected function calculateSurcharge()
	{
		if (!strlen($this->surcharge_field))
			return 0;
			
		$intSurcharge = 0;
		$arrProducts = $this->Cart->getProducts();
		
		foreach( $arrProducts as $objProduct )
		{
			// Exclude this product if table does not have this field
			if ($this->Database->fieldExists($this->surcharge_field, 'tl_product_data'))
			{
				$strSurcharge = $this->Database->prepare("SELECT * FROM tl_product_data WHERE id=?")
											   ->limit(1)
											   ->execute($product['id'])
											   ->{$this->surcharge_field};
											   
				if ($this->flatCalculation == 'perItem')
				{
					$intSurcharge += ($product['quantity_requested'] * floatval($strSurcharge));
				}
				else
				{
					$intSurcharge += floatval($strSurcharge);
				}
			}
		}
		
		return $intSurcharge;
	}
	
	/**
	 * Builds the XML used to make the request
	 * 
	 * If $customer_context is an array it should be in the format:
	 * $customer_context = array('Element' => 'Value');
	 * 
	 * @access public
	 * @param array|string $cutomer_context customer data
	 * @return string $return_value request XML
	 */
	public function buildRequest($customer_context = null) {
		// create the new dom document
		$xml = new DOMDocument('1.0', 'utf-8');
		
	
		$access_element = $xml->appendChild(
			new DOMElement('AccessRequest'));
		$access_element->setAttributeNode(new DOMAttr('xml:lang', 'en-US'));
		
		// create the child elements
		$access_element->appendChild(
			new DOMElement('AccessLicenseNumber', $this->ups_accessKey));
		$access_element->appendChild(
			new DOMElement('UserId', $this->ups_userName));
		$access_element->appendChild(
			new DOMElement('Password', $this->ups_password));
						
		/** create the AddressValidationRequest element **/
		$rate = $xml->appendChild(
			new DOMElement('RatingServiceSelectionRequest'));
		$rate->setAttributeNode(new DOMAttr('xml:lang', 'en-US'));
		
		// create the child elements
		$request = $this->buildRequest_RequestElement($rate,
			'Rate', 'Rate', $customer_context);
		
		
		/** build the pickup type node **/
		$pickup_type = $rate->appendChild(new DOMElement('PickupType'));
		$pickup_type->appendChild(new DOMElement('Code',
			$this->shipment['pickup_type']['code']));
		$pickup_type->appendChild(new DOMElement('Description',
			$this->shipment['pickup_type']['description']));
		
		$shipment = $rate->appendChild(new DOMElement('Shipment'));
		
		$this->buildRequest_Shipper($shipment);
		$this->buildRequest_Destination($shipment);
		$this->buildRequest_ShipFrom($shipment);
		$shipment = $this->buildRequest_Shipment($shipment);
		
		return $xml->saveXML();
	} // end function buildRequest()
	
	/**
	 * Send a request to the UPS Server using xmlrpc
	 * 
	 * @access public
	 * @params string $request_xml XML request from the child objects
	 * buildRequest() method
	 * @params boool $return_raw_xml whether or not to return the raw XML from
	 * the request
	 * 
	 * @todo remove array creation after switching over to xpath
	 */
	public function sendRequest($request_xml, $return_raw_xml = true) 
	{

		  $ch = curl_init($this->server);  
                curl_setopt($ch, CURLOPT_HEADER, 0);  
                curl_setopt($ch,CURLOPT_POST,1);  
                curl_setopt($ch,CURLOPT_TIMEOUT, 60);  
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
                curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
                curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
                curl_setopt($ch,CURLOPT_POSTFIELDS,$request_xml);  
                $response=curl_exec ($ch);  

                // Find out if the UPS service is down
                preg_match_all('/HTTP\/1\.\d\s(\d+)/',$result,$matches);
                foreach($matches[1] as $key=>$value) {
                    if ($value != 100 && $value != 200) {
                        throw new Exception("The UPS service seems to be down with HTTP/1.1 $value");
                    }
                }
                
                

        curl_close($ch);  
        
        $response = trim($response);
        $response = utf8_encode($response);        
		
		
		// create the context stream and make the request
		/*$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: text/xml',
				'content' => $request_xml,
			),
		));
		$response = file_get_contents($this->server, false, $context);
		*/
		// TODO: remove array creation after switching over to xpath
		// create an array from the raw XML data
		
		// build the dom objects
		$this->response = new DOMDocument();
		$this->response->loadXML($response);
		
		$this->xpath = new DOMXPath($this->response);
		$this->root_node = $this->xpath->query(
			'/'.$this->getRootNodeName())->item(0);
		
		// check if we should return the raw XML data
		if ($return_raw_xml) {
			return $response;
		} // end if we should return the raw XML
		
		// return the response as an array
		return $this->response_array;
	} // end function sendRequest()


		/**
	 * Returns the error message(s) from the response
	 * 
	 * @return array
	 */
	public function getError() {
		// iterate over the error messages
		$errors = $this->xpath->query('Response/Error', $this->root_node);
		$return_value = array();
		foreach ($errors as $error) {
			$return_value[] = array(
				'severity' => $this->xpath->query('ErrorSeverity', $error)
					->item(0)->nodeValue,
				'code' => $this->xpath->query('ErrorCode', $error)
					->item(0)->nodeValue,
				'description' => $this->xpath->query('ErrorDescription', $error)
					->item(0)->nodeValue,
				'location' => $this->xpath
					->query('ErrorLocation/ErrorLocationElementName', $error)
					->item(0)->nodeValue,
			); // end $return_value
		} // end for each error message
		
		return $return_value;
	} // end function getError()
	
	/**
	 * Checks to see if a repsonse is an error
	 * 
	 * @access public
	 * @return boolean 
	 */
	public function isError() {
		// check to see if the request failed
		$status = $this->xpath->query('Response/ResponseStatusCode',
			$this->root_node);
		if ($status->item(0)->nodeValue == $this->intStatusFail) {
			return true;
		} // end if the request failed
		
		return false;
	} // end function isError
	
	
	/**
	 * Builds the Request element
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @param string $action
	 * @param string $option
	 * @param string|array $customer_context
	 * @return DOMElement
	 */
	protected function buildRequest_RequestElement(&$dom_element, $action,
		$option = null, $customer_context = null) {
		// create the child element
		$request = $dom_element->appendChild(
			new DOMElement('Request'));
		
		// create the children of the Request element
		$transaction_element = $request->appendChild(
			new DOMElement('TransactionReference'));
		$request->appendChild(
			new DOMElement('RequestAction', $action));
		
		// check to see if an option was passed in
		if (!empty($option)) {
			$request->appendChild(
				new DOMElement('RequestOption', $option));
		} // end if an option was passed in
		
		// create the children of the TransactionReference element
		$transaction_element->appendChild(
			new DOMElement('XpciVersion', '1.0'));
		
		// check if we have customer data to include
		if (!empty($customer_context)) {
			// check to see if the customer context is an array
			if (is_array($customer_context)) {
				$customer_element = $transaction_element->appendChild(
					new DOMElement('CustomerContext'));

				// iterate over the array of customer data
				foreach ($customer_context as $element => $value) {
					$customer_element->appendChild(
						new DOMElement($element, $value));
				} // end for each customer data
			} // end if the customer data is an array
			else {
				$transaction_element->appendChild(
					new DOMElement('CustomerContext', $customer_context));
			} // end if the customer data is a string
		} // end if we have customer data to include
		
		return $request;
	} // end function buildRequest_RequestElement()
	

	/**
	 * Builds the destination elements
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @return DOMElement
	 */
	protected function buildRequest_Destination(&$dom_element) {
		/** build the destination element and its children **/
		$destination = $dom_element->appendChild(new DOMElement('ShipTo'));
		$destination->appendChild(new DOMElement('CompanyName',
			$this->destination['name']));
		$destination->appendChild(new DOMElement('PhoneNumber',
			$this->destination['phone']));
		$address = $destination->appendChild(new DOMElement('Address'));
		
		
		/** build the address elements children **/
		$address->appendChild(new DOMElement('AddressLine1',
			$this->destination['street']));
		
		// check to see if there is a second steet line
		if (isset($this->destination['street2']) &&
			!empty($this->destination['street2'])) {
			$address->appendChild(new DOMElement('AddressLine2',
				$this->destination['street2']));
		} // end if there is a second street line
		
		// check to see if there is a third steet line
		if (isset($this->destination['street3']) &&
			!empty($this->destination['street3'])) {
			$address->appendChild(new DOMElement('AddressLine3',
				$this->destination['street3']));
		} // end if there is a second third line
		
		// build the rest of the address
		$address->appendChild(new DOMElement('City',
			$this->destination['city']));
		$address->appendChild(new DOMElement('StateProvinceCode',
			$this->destination['state']));
		$address->appendChild(new DOMElement('PostalCode',
			$this->destination['zip']));
		$address->appendChild(new DOMElement('CountryCode',
			$this->destination['country']));
		
		return $destination;
	} // end function buildRequest_Destination()
	
	/**
	 * Buildes the package elements
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @param array $package
	 * @return DOMElement
	 * 
	 * @todo determine if the package description is needed
	 */
	protected function buildRequest_Package(&$dom_element, $package) {
		/** build the package and packaging type **/
		$package_element = $dom_element->appendChild(new DOMElement('Package'));
		$packaging_type = $package_element->appendChild(
			new DOMElement('PackagingType'));
		$packaging_type->appendChild(new DOMElement('Code',
			$package['packaging']['code']));
		$packaging_type->appendChild(new DOMElement('Description',
			$package['packaging']['description']));
		
		// TODO: determine if we need this
		$package_element->appendChild(new DOMElement('Description',
			$package['description']));
		
		
		/** build the package weight **/
		$package_weight = $package_element->appendChild(
			new DOMElement('PackageWeight'));
		$units = $package_weight->appendChild(
			new DOMElement('UnitOfMeasurement'));
		$units->appendChild(new DOMElement('Code', $package['units']));
		$package_weight->appendChild(
			new DOMElement('Weight', $package['weight']));
		
		return $package_element;
	} // end function buildRequest_Package()
	
	/**
	 * Builds the service options node
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @return boolean|DOMElement
	 */
	protected function buildRequest_ServiceOptions(&$dom_element) {
		// build our elements
		$service_options = $dom_element->appendChild(
			new DOMElement('ShipmentServiceOptions'));
		$on_call_air = $service_options->appendChild(
			new DOMElement('OnCallAir'));
		$schedule = $on_call_air->appendChild(new DOMElement('Schedule'));
		
		// check to see if this is a satruday pickup
		if (isset($this->shipment['saturday']['pickup']) &&
			$this->shipment['saturday']['pickup'] !== false) {
			$service_options->appendChild(new DOMElement('SaturdayPickup'));
		} // end if this is a saturday pickup
		
		// check to see if this is a saturday delivery
		if (isset($this->shipment['saturday']['delivery']) &&
			$this->shipment['saturday']['delivery'] !== false) {
			$service_options->appendChild(new DOMElement('SaturdayDelivery'));
		} // end if this is a saturday delivery
		
		// check to see if we have a pickup day
		if (isset($this->shipment['pickup_day'])) {
			$schedule->appendChild(new DOMElement('PickupDay',
				$this->shipment['pickup_day']));
		} // end if we have a pickup day
		
		// check to see if we have a scheduling method
		if (isset($this->shipment['scheduling_method'])) {
			$schedule->appendChild(new DOMElement('Method',
				$this->shipment['scheduling_method']));
		} // end if we have a scheduling method
		
		// check to see if we have on call air options
		if (!$schedule->hasChildNodes()) {
			$service_options->removeChild($on_call_air);
		} // end if we have on call air options
		
		// check to see if we have service options
		if (!$service_options->hasChildNodes()) {
			$dom_element->removeChild($service_options);
			return false;
		} // end if we do not have service options
		
		return $service_options;
	} // end function buildRequest_ServiceOptions()
	
	/**
	 * Builds the ship from elements
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @return DOMElement
	 */
	protected function buildRequest_ShipFrom(&$dom_element) {
		/** build the destination element and its children **/
		$ship_from = $dom_element->appendChild(new DOMElement('ShipFrom'));
		$ship_from->appendChild(new DOMElement('CompanyName',
			$this->ship_from['name']));
		$ship_from->appendChild(new DOMElement('PhoneNumber',
			$this->ship_from['phone']));
		$address = $ship_from->appendChild(new DOMElement('Address'));
		
		
		/** build the address elements children **/
		$address->appendChild(new DOMElement('AddressLine1',
			$this->ship_from['street']));
		
		// check to see if there is a second steet line
		if (isset($this->ship_from['street2']) &&
			!empty($this->ship_from['street2'])) {
			$address->appendChild(new DOMElement('AddressLine2',
				$this->ship_from['street2']));
		} // end if there is a second street line
		
		// check to see if there is a third steet line
		if (isset($this->ship_from['street3']) &&
			!empty($this->ship_from['street3'])) {
			$address->appendChild(new DOMElement('AddressLine3',
				$this->ship_from['street3']));
		} // end if there is a second third line
		
		// build the rest of the address
		$address->appendChild(new DOMElement('City',
			$this->ship_from['city']));
		$address->appendChild(new DOMElement('StateProvinceCode',
			$this->ship_from['state']));
		$address->appendChild(new DOMElement('PostalCode',
			$this->ship_from['zip']));
		$address->appendChild(new DOMElement('CountryCode',
			$this->ship_from['country']));
		
		return $ship_from;
	} // end function buildRequest_ShipFrom()
	
	/**
	 * Builds the shipment elements
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @return DOMElement
	 */
	protected function buildRequest_Shipment(&$shipment) {
		
		/** build the shipment node **/
		$service = $shipment->appendChild(new DOMElement('Service'));
		$service->appendChild(new DOMElement('Code',
			$this->shipment['service']));
		
		// iterate over the pacakges to create the package element
		foreach ($this->shipment['packages'] as $package) {
			$this->buildRequest_Package($shipment, $package);
		} // end for each package
		
		$this->buildRequest_ServiceOptions($shipment);
		
		return $shipment;
	} // end function buildRequest_Shipment()
	
	/**
	 * Builds the shipper elements
	 * 
	 * @access protected
	 * @param DOMElement $dom_element
	 * @return DOMElement
	 */
	protected function buildRequest_Shipper(&$dom_element) {
		/** build the destination element and its children **/
		$shipper = $dom_element->appendChild(new DOMElement('Shipper'));
		$shipper->appendChild(new DOMElement('Name',
			$this->shipper['name']));
		$shipper->appendChild(new DOMElement('PhoneNumber',
			$this->shipper['phone']));
		
		// check to see if we have a shipper number
		if (isset($this->shipper['number']) &&
			!empty($this->shipper['number'])) {
			$shipper->appendChild(new DOMElement('ShipperNumber',
				$this->shipper['number']));
		} // end if we have a shipper number
		
		$address = $shipper->appendChild(new DOMElement('Address'));
		
		
		/** build the address elements children **/
		$address->appendChild(new DOMElement('AddressLine1',
			$this->shipper['street']));
		
		// check to see if there is a second steet line
		if (isset($this->shipper['street2']) &&
			!empty($this->shipper['street2'])) {
			$address->appendChild(new DOMElement('AddressLine2',
				$this->shipper['street2']));
		} // end if there is a second street line
		
		// check to see if there is a third steet line
		if (isset($this->shipper['street3']) &&
			!empty($this->shipper['street3'])) {
			$address->appendChild(new DOMElement('AddressLine3',
				$this->shipper['street3']));
		} // end if there is a second third line
		
		// build the rest of the address
		$address->appendChild(new DOMElement('City',
			$this->shipper['city']));
		$address->appendChild(new DOMElement('StateProvinceCode',
			$this->shipper['state']));
		$address->appendChild(new DOMElement('PostalCode',
			$this->shipper['zip']));
		$address->appendChild(new DOMElement('CountryCode',
			$this->shipper['country']));
		
		return $shipper;
	} // end function buildRequest_Shipper()
	
	/**
	 * Returns any warnings from the response
	 * 
	 * @return array
	 */
	public function getWarnings() {
		$warnings = $this->xpath->query(self::NODE_NAME_RATED_SHIPMENT.
			'/RatedShipmentWarning', $this->root_node);
		
		// iterate over the warnings
		$return_value = array();
		foreach ($warnings as $warning) {
			$return_value[] = $warning->nodeValue;
		} // end for each warning
		
		return $return_value;
	} // end function getWarnings()
	

	
	/**
	 * Returns the name of the servies response root node
	 * 
	 * @access protected
	 * @return string
	 * 
	 * @todo remove after phps self scope has been fixed
	 */
	protected function getRootNodeName() {
		return NODE_NAME_ROOT_NODE;
	} // end function getRootNodeName()
	
	
	
	
}

