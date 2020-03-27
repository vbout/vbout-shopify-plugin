<?php

namespace App\Libraries;

use App\Http\Controllers\MapFieldsController;
use App\Libraries\Shopify\ShopfiyFields;
use App\Libraries\Vbout\Services\EcommerceWS;
use App\Models\Setting;
use App\Models\Shop;
use App\Libraries\Vbout\Services\EmailMarketingWS;
use Prophecy\Exception\Prediction\AggregateException;
use function Sodium\add;
use DB;
class VboutifyV2
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
        DB::table('logging')->insert(
            [
                'data' => 'NONE',
                'step' => 0,
                'comment' => 'Init2'
            ]
        );
        $this->getFieldMaps($request);
    }

    /**
     * Get Type from header
     * @param  $evemt
     * @returns int  $type
     */
    private function getFieldMaps($request)
    {

        $shopifyFields = new ShopfiyFields();
        $shopifyMapFields = new MapFieldsController();
        $event = $request->header('X-Shopify-Topic');
        $shopUrl = $request->header('X-Shopify-Shop-Domain');
        $settings = $this->loadSettings($shopUrl);
        $sendData = new EcommerceWS(['api_key' => $settings->apiKey]);

        $domain = $settings->domain;
        $action = 0 ; // 1 for Create  , 2 for update , 3 for deletion
        DB::table('logging')->insert(
            [
                'data' => 'NONE',
                'step' => 0,
                'comment' => 'Init'
            ]
        );

        $event = 'orders/create';
        switch ($event) {
            case 'customers/create':
                DB::table('logging')->insert(
                    [
                        'data' => $request,
                        'step' => 1.1,
                        'comment' => 'Before getCustomerAloneFieldMapfields'
                    ]
                );
                 $mappedFields = $shopifyFields->getCustomerAloneFieldMap();
                 DB::table('logging')->insert(
                [
                    'data' => 'asd',
                    'step' => 1.2,
                    'comment' => 'Before Shopfiy Mapping fields'
                ]
            );

                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                DB::table('logging')->insert(
                    [
                        'data' => '23515',
                        'step' => 1,
                        'comment' => 'after Shopfiy Mapping fields'
                    ]
                );
                $action = 1;
                $dataFields['domain'] = $domain;
                DB::table('logging')->insert(
                    [
                        'data' => serialize($dataFields),
                        'step' => 10,
                        'comment' => 'Final step before insert'
                    ]
                );
                $sendData = $sendData->Customer($dataFields,$action);
                break;
            case 'customers/update':
                DB::table('logging')->insert(
                    [
                        'data' => $request,
                        'step' => 1.1,
                        'comment' => 'Before getCustomerAloneFieldMapfields'
                    ]
                );
                 $mappedFields = $shopifyFields->getCustomerAloneFieldMap();
                 DB::table('logging')->insert(
                [
                    'data' => 'asd',
                    'step' => 1.2,
                    'comment' => 'Before Shopfiy Mapping fields'
                ]
            );

                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                DB::table('logging')->insert(
                    [
                        'data' => '23515',
                        'step' => 1,
                        'comment' => 'after Shopfiy Mapping fields'
                    ]
                );
                $action = 2;
                $dataFields['domain'] = $domain;
                DB::table('logging')->insert(
                    [
                        'data' => serialize($dataFields),
                        'step' => 10,
                        'comment' => 'Final step before insert'
                    ]
                );
                $sendData = $sendData->Customer($dataFields,$action);
                break;
            case 'orders/create':
                $path = public_path().'\data.json';
                $data = json_decode(file_get_contents($path), true);

                DB::table('logging')->insert(
                    [
                        'data' => $path,
                        'step' => 1.1,
                        'comment' => 'Before getOrderFieldMap'
                    ]
                );

//                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                // end for testing purposes
//                $mappedFields = $shopifyFields->getCustomerFieldMap();
//                $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
//                $mappedFields = $shopifyFields->getAddressMapFields(1);
//                $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
//                $mappedFields = $shopifyFields->getAddressMapFields(2);
//                $dataFields['shipping_address'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);



                // for testing purposes
                $path = public_path().'\data.json';
                $data = json_decode(file_get_contents($path), true);

                DB::table('logging')->insert(
                    [
                        'data' => 'init',
                        'step' => 1.1,
                        'comment' => 'Before getOrderFieldMap'
                    ]
                );
                $mappedFields = $shopifyFields->getOrderFieldMap($shopUrl);

                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                DB::table('logging')->insert(
                    [
                        'data' => serialize($dataFields),
                        'step' => 1.2,
                        'comment' => 'Before getCustomerFieldMap'
                    ]
                );
                $mappedFields = $shopifyFields->getCustomerFieldMap();
                $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($data, $mappedFields);
                DB::table('logging')->insert(
                    [
                        'data' => serialize($dataFields),
                        'step' => 1.3,
                        'comment' => 'Before getAddressMapFields'
                    ]
                );
                $mappedFields = $shopifyFields->getAddressMapFields(1);
                $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($data, $mappedFields);
                DB::table('logging')->insert(
                    [
                        'data' => serialize($dataFields),
                        'step' => 1.4,
                        'comment' => 'Before getAddressMapFields 3 '
                    ]
                );
                $mappedFields = $shopifyFields->getAddressMapFields(2);
                $dataFields['shipping_address'] = $shopifyMapFields->ShopifyMapFields($data, $mappedFields);

                $action = 1;

                $dataFields['domain'] = $domain;
                DB::table('logging')->insert(
                    [
                        'data' => serialize($dataFields),
                        'step' => 1.5,
                        'comment' => 'Before send'
                    ]
                );
                $sendData = $sendData->Order($dataFields,$action);
                break;
            case 'orders/paid':
                $type = 3;
                break;
            case 'orders/updated':
                $mappedFields = $shopifyFields->getOrderFieldMap($shopUrl);
                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getCustomerFieldMap();
                $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getAddressMapFields(1);
                $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getAddressMapFields(2);
                $dataFields['shipping_address'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $dataFields['action'] = 2;
                $dataFields['domain'] = $domain;
                $sendData = $sendData->Order($dataFields,$action);
                break;
            case 'checkouts/create':
                $mappedFields = $shopifyFields->getOrderFieldMap($shopUrl);
                break;
            case 'checkouts/update':
                break;
            case 'cart/add':
                $mappedFields = $shopifyFields->getCartFieldMap();
                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields  = $shopifyFields->getCustomerFieldMap();
                $dataFields ['customerinfo']= $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $dataFields['action'] = 1;
                $dataFields['domain'] = $domain;
                $sendData = $sendData->CartItem($dataFields,$action);
                break;
            case 'cart/update':
                $mappedFields = $shopifyFields->getCartFieldMap();
                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields  = $shopifyFields->getCustomerFieldMap();
                $dataFields ['customerinfo']= $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $dataFields['action'] = 1;
                $dataFields['domain'] = $domain;
                $sendData = $sendData->CartItem($dataFields,$action);
                break;
            default:
                $type = 0;
                break;
        }
        return $mappedFields;
    }

    /**
     * Mapps the parameter and sends them to a global function
     * @int  $type
     * @param  $data
     * @string  $shop_url
     * @int  $listId
     * @returns string  $result
     */
    private function mappingAPI($type, $data, $shop_id, $listId, $spiKey)
    {
        $activeSettings = new Setting();
        $activeSettings = $activeSettings->getListActiveSettings($shop_id);

        //To send to the global function
        $address = $this->getaddressData($data);
        $products = $this->getCustomerData($data);
        $customer = $this->getProductData($data);

        $customerEmail = '';
        if (isset($data['email'])) {
            $customerEmail = $data['email'];
        }else if (isset($data['customer']['email'])) {
            $customerEmail = $data['customer']['email'];
        }

        if(isset($address))
            $data['address'] = $address;
        if(isset($customer))
            $data['customer'] = $customer;
        if(isset($products))
            $data['$products'] = $products;
        if(isset($data))
        {
            $payload = [
                'email' => $customerEmail,
                'listId' => $listId,
                'status' => 'active',
                'data'   => $data
            ];

            $vboutApp = new EcommerceWS(['api_key' => $spiKey]);
            $result = $vboutApp->sendEcommerce($type, $payload);
        }

    }
    private function getCustomerData($data)
    {
        //Setting customer Email
        $customer = [];
        if (isset($data['customer'])) {
            $customer = $data['customer']; //if from Checkouts or Orders webhook
        } else {
            $customer = $data; //if from Customers webhook
        }
        return $customer;
    }
    private function getProductData($data)
    {
        $products = [];

        return $products;
    }

    private function getaddressData($data)
    {
        $address=[];


        return $address;
    }

    private function loadSettings($shopUrl)
    {

        $settings = Shop::where('shop_url', $shopUrl)->first();

        return $settings;
    }

}
