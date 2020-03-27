<?php

namespace App\Libraries;

use App\Models\Shop;
use App\Libraries\Vbout\Services\EmailMarketingWS;
use DB;
use App\Libraries\VboutifyV2;

class Vboutify
{
	/*
	WEBHOOKS EVENT DATA:
	Customers:	email,created_at,first_name,last_name,state,total_spent,last_order_id,phone
	
	Checkouts: 	email,created_at,total_price,currency,completed_at,phone,
				line_items(title-quantity),abandoned_checkout_url,
				shipping_address(first_name-last_name-phone-city-zip-province-country),
				customer(email-first_name-last_name-state-total_spent-last_order_id-phone)
				
	Orders:		email,created_at,total_price,currency,order_number,
				line_items(title-quantity),
				shipping_address(first_name-last_name-phone-city-zip-province-country),
				customer(email-first_name-last_name-state-total_spent-last_order_id-phone)

	*/
	
    public function start($request)
    {
        $event = $request->header('x-shopify-topic');
        $shopUrl = $request->header('x-shopify-shop-domain');
        $settings = $this->loadSettings($shopUrl);
        $result = 'No settings provided';
        DB::table('logging')->insert(
            [
                'data' => $event,
                'step' => 0,
                'comment' => 'Init2'
            ]
        );
        if ($settings->newShop == 1 )
        {
            $vboutify = new VboutifyV2();
            $result = $vboutify->start($request);
            return response()->json(['message' => $result]);
        }

        if ($settings && $settings->apiKey) {
            $canUpdate = false;

            switch ($event) {
                case 'customers/create':
                    $listId = $settings->customersList->id;
                    $listFields = isset($settings->customersList->fields) ? $settings->customersList->fields : [];
                    break;
                case 'orders/create': 
                case 'orders/paid': 
                case 'orders/updated': 
                    $listId = $settings->completePurchasesList->id;
                    $listFields = isset($settings->completePurchasesList->fields) ? $settings->completePurchasesList->fields : [];
                    $canUpdate = true;
                    break;
                case 'checkouts/create':
                    if (!$request->gateway) {
                        $listId = $settings->incompletePurchasesList->id;
                        $listFields = isset($settings->incompletePurchasesList->fields) ? $settings->incompletePurchasesList->fields : [];
                    }
					break;
                case 'checkouts/update':
                    if (!$request->gateway) {
                        $listId = $settings->incompletePurchasesList->id;
                        $listFields = isset($settings->incompletePurchasesList->fields) ? $settings->incompletePurchasesList->fields : [];
                    } else {
						$listId = false;
						$listFields = false;
                        //$listId = $settings->completePurchasesList->id;
                        //$listFields = isset($settings->completePurchasesList->fields) ? $settings->completePurchasesList->fields : [];
                    }                    
                    $canUpdate = true;
                    break;
                default:
                    $listId = false;
                    $listFields = false;
                    break;
            }

            if (!$listId) {
                $result = 'List ID does not exists';
            } else {
                try {
                    // Get webhook data
                    $data = $request->all();

                    $customerEmail = '';
                    if (isset($data['email'])) {
						$customerEmail = $data['email'];
                    }else if (isset($data['customer']['email'])) {
                        $customerEmail = $data['customer']['email'];
                    }

                    // $customer = (isset($data['customer'])) ? $data['customer'] : $data;
                    $customer = [];                    
					if (isset($data['customer'])) {
						$customer = $data['customer']; //if from Checkouts or Orders webhook
					} else {
						$customer = $data; //if from Customers webhook
					}
					
					$address = [];
					if (isset($data['shipping_address'])) {
                        $address = $data['shipping_address']; //if from Checkouts or Orders webhook
                    }
					
                    $products = [];
					$products['total_amount'] = '';
					$products['cart_items'] = '';
					$products['created_at'] = '';
					$products['abandoned_checkout_url'] = '';
					$products['order_number'] = '';
					$products['order_name'] = '';
					$products['number'] = '';
					$products['event'] = $event;
					
					if(isset($data['number'])){
						$products['number'] = $data['number'];
					}
					if(isset($data['name'])){
						$products['order_name'] = $data['name'];
					}
					if(isset($data['order_number'])){
						$products['order_number'] = $data['order_number'];
					}
					
					
					if(isset($data['completed_at'])){
						$products['created_at'] = $data['completed_at']; //if from Checkouts webhook
					}else if(isset($data['created_at'])){
						$products['created_at'] = $data['created_at'];
					}
					
					if(isset($data['abandoned_checkout_url'])){
						$products['abandoned_checkout_url'] = $data['abandoned_checkout_url'];
					}
					
					if(isset($data['total_price'])){
						$products['total_amount'] = $data['total_price'].' '.$data['currency'];
					}
					if(isset($data['line_items'])){
						if(count($data['line_items'])>0){
							$productname = '';
							for($i=0;$i<count($data['line_items']);$i++){
								if($productname=='')
									$productname .= 'Item#'.($i+1).': '.$data['line_items'][$i]['title'].' (QTY '.$data['line_items'][$i]['quantity'].')';
								else
									$productname .= ', Item#'.($i+1).': '.$data['line_items'][$i]['title'].' (QTY '.$data['line_items'][$i]['quantity'].')';
							}
							$products['cart_items'] = $productname;
						}
					}

                    // $address = (isset($customer['shipping_address'])) ? $customer['shipping_address'] : [];
					
					$fields = [];
                    if ($listFields) {
                        // Map the fields
						$fields = $this->mapFields($listFields, $customer, $products, $address);                  
                    }

                    // Vbout API for Email Marketing
                    $vboutApp = new EmailMarketingWS(['api_key' => $settings->apiKey]);

                    // Request payload
                    $payload = [
                        'email' => $customerEmail,
                        'status' => 'Active',
                        'listid' => $listId,
                        'fields' => $fields
                    ];

                    if ($canUpdate) {
                        // Check if contact exists in incomplete purchases
                        $contact = $vboutApp->searchContact($customerEmail, $settings->incompletePurchasesList->id);

                        // Add contact to complete purchase
                        if (isset($contact['id']) && $contact['id'] !== '') {
                            $payload['id'] = $contact['id'];
                            $result = $vboutApp->updateContact($payload);
                        } else {
                            $result = $vboutApp->addNewContact($payload);
                        }
                    } else {
                        $result = $vboutApp->addNewContact($payload);
                    }

                    // Additional customer sync when checking out
                    if ($event === 'checkouts/update' && $customerEmail !== '') {
                        // Check if contact exists in customers list
                        $cContact = $vboutApp->searchContact($customerEmail, $settings->customersList->id);

                        $cFields = [];
                        if (isset($settings->customersList->fields)) {
                            $cFields = $this->mapFields($settings->customersList->fields, $customer, $products, $address);
                        }

                        // Add contact to customers list
                        $cPayload = [
                            'email' => $customerEmail,
                            'status' => 'Active',
                            'listid' => $settings->customersList->id,
                            'fields' => $cFields
                        ];

                        if (isset($cContact['id']) && $cContact['id'] !== '') {
                            $cPayload['id'] = $cContact['id'];
                            $result = $vboutApp->updateContact($cPayload);
                        } else {
                            $result = $vboutApp->addNewContact($cPayload);
                        }
                    }

                    // Newsletter subscription
                    if (isset($request->customer['accepts_marketing']) && $event === 'orders/create') {
                        // Check if contact exists in newsletter list
                        $nlContact = $vboutApp->searchContact($customerEmail, $settings->newsLettersList->id);

                        $nlFields = [];
                        if (isset($settings->newsLettersList->fields)) {
                            $nlFields = $this->mapFields($settings->newsLettersList->fields, $customer, $products, $address);
                        }

                        // Add contact to newsletter list
                        $nlPayload = [
                            'email' => $customerEmail,
                            'status' => 'Active',
                            'listid' => $settings->newsLettersList->id,
                            'fields' => $nlFields
                        ];

                        if (isset($nlContact['id']) && $nlContact['id'] !== '') {
                            if (!$request->customer['accepts_marketing']) {
                                $result = $vboutApp->removeContact($nlContact['id']);
                            } else {
                                $nlPayload['id'] = $nlContact['id'];
                                $result = $vboutApp->updateContact($nlPayload);
                            }
                        } else {
                            if ($request->customer['accepts_marketing']) {
                                $result = $vboutApp->addNewContact($nlPayload);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $result = $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine();
                }
            }
        }

        return $result;
    }

    public function loadSettings($shopUrl)
    {
        $shop = Shop::where('shop_url', $shopUrl)->first();
        if ($shop->newShop == 1 )
            $settings = $shop;
        else
            $settings = ($shop) ? json_decode($shop->settings) : null;

        return $settings;
    }

    /*public function mapFields($listFields, $customer)
    {
        $fields = [];
        if ($listFields) {
            // Map the fields
			$fieldcnt = 0;
            foreach ($listFields as $field) {
                if ($field !== '') {
                    list($fieldKey, $fieldVal) = explode('|', $field);
                    //$fieldName = preg_replace('/ /', '_', strtolower($fieldVal));
					
					if($fieldcnt==0) $fieldName = 'first_name'; 		//first_name
					else if($fieldcnt==1) $fieldName = 'last_name';		//last_name
					else if($fieldcnt==2) $fieldName = 'country';		//country
					else if($fieldcnt==3) $fieldName = 'province';		//state
					else if($fieldcnt==4) $fieldName = 'phone';			//phone
					
                    // For customer fields
                    if (isset($customer[$fieldName])) {
                        $fields[$fieldKey] = $customer[$fieldName];
                    }
                }
				$fieldcnt++;
            }
        }

        return $fields;
    }*/
	
	public function mapFields($listFields, $customer, $products, $address)
    {
        $fields = [];
        if ($listFields) {
            // Map the fields
			$fieldcnt = 0;
            foreach ($listFields as $field) {
				$fieldvalue = '';
                if ($field !== '') {
                    list($fieldKey, $fieldVal) = explode('|', $field);
                    //$fieldName = preg_replace('/ /', '_', strtolower($fieldVal));
					//first_name,last_name,country,state,city,zipcode,phone,lifetime_spending,	last_order_number,last_order_total,last_order_date,cart_items,	cart_recovery_url
                    if($fieldcnt==0) $fieldName = 'first_name'; 			//first_name
					else if($fieldcnt==1) $fieldName = 'last_name';			//last_name
					else if($fieldcnt==2) $fieldName = 'country';			//country
					else if($fieldcnt==3) $fieldName = 'province';			//state
					else if($fieldcnt==4) $fieldName = 'city';				//city
					else if($fieldcnt==5) $fieldName = 'zip';				//zipcode
					else if($fieldcnt==6) $fieldName = 'phone';				//phone					
					else if($fieldcnt==7) $fieldName = 'total_spent';		//lifetime_spending
					
					else if($fieldcnt==8) $fieldName = 'order_name';				//#order_id
					//else if($fieldcnt==8) $fieldName = 'order_number';		//last_order_number
					//else if($fieldcnt==8) $fieldName = 'last_order_name';				//last_order_number
					//else if($fieldcnt==8) $fieldName = 'event';				//last_order_number
					else if($fieldcnt==9) $fieldName = 'total_amount';		//last_order_total
					else if($fieldcnt==10) $fieldName = 'created_at';		//last_order_date
					else if($fieldcnt==11) $fieldName = 'cart_items';		//cart_items
					else if($fieldcnt==12) $fieldName = 'abandoned_checkout_url';	//cart_recovery_url
					
					if($fieldcnt<=7){
						// For customer/address fields
						if (isset($customer[$fieldName])) {
							$fieldvalue = $customer[$fieldName];
						}else if(isset($address[$fieldName])) {
							$fieldvalue = $address[$fieldName];
						}
					}else{
						// For products/order fields
						if (isset($products[$fieldName])) {
							$fieldvalue = $products[$fieldName];
						}
					}						
					$fields[$fieldKey] = $fieldvalue;
                }
				$fieldcnt++;
            }
        }

        return $fields;
    }
}