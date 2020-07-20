<?php

namespace App\Libraries;

use App\Http\Controllers\MapFieldsController;
use App\Libraries\Shopify\Services\Products;
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

    public function start($request){
        $this->getFieldMaps($request);
    }

    /**
     * Get Type from header
     * @param  $evemt
     * @returns int  $type
     */
    private function getFieldMaps($request){

        $shopifyFields = new ShopfiyFields();
        $shopifyMapFields = new MapFieldsController();
        $event = $request->header('X-Shopify-Topic');
        $shopUrl = $request->header('X-Shopify-Shop-Domain');
        $shop = $this->loadShop($shopUrl);
        $sendData = new EcommerceWS(['api_key' => $shop->apiKey]);
        $settings = $this->loadSettings($shop->id);
        $domain = $shop->domain;
        $action = 0 ; // 1 for Create  , 2 for update , 3 for deletion

        DB::table('logging')->insert(
            [
                'data' => json_encode($request->all()),
                'step' => 1,
                'comment' => $event .' init'
            ]
        );

        switch ($event) {
            case 'customers/create':
                if($settings->customers == 1)
                {
                    $mappedFields = $shopifyFields->getCustomerAloneFieldMap();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                    $action = 1;
                    $dataFields['domain'] = $domain;
                    $dataFields['ipaddress'] = $_SERVER['REMOTE_ADDR'];
                    $sendData->Customer($dataFields,$action);
                }
                break;
            case 'customers/update':
                if($settings->customers == 1) {
                    $mappedFields = $shopifyFields->getCustomerAloneFieldMap();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                    $action = 1;
                    $dataFields['domain'] = $domain;
                    $dataFields['ipaddress'] = $_SERVER['REMOTE_ADDR'];

                    $sendData->Customer($dataFields, $action);
                }
                 break;

            case 'checkouts/create':
                if($settings->abandoned_carts == 1) {
                    try {
                    $mappedFields = $shopifyFields->getCheckoutFiedlMap();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFields = $shopifyFields->getCustomerFieldMap();
                    $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFieldsCreateCart = $shopifyFields->getCartBasicFieldMap();
                    $dataFieldsCart = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFieldsCreateCart);

                    $action = 1;

                    $dataFields['domain'] = $domain;

                    $dataFieldsCart['customerinfo'] = $dataFields['customerinfo'];
                    $dataFieldsCart['domain'] = $dataFields['domain'];
                    $dataFieldsCart['customer'] = $dataFieldsCart['customerinfo']["email"];
                    $dataFieldsCart['storename'] = $request->input('line_items')[0]['vendor'];

                    $sendData->Cart($dataFieldsCart, $action);

                    $line_items = $request->input('line_items');

                    $removCartItem['domain'] = $dataFields['domain'];
                    $removCartItem['cartid'] = $dataFields['cartid'];
                    $sendData->CartItem($removCartItem, 3);

                    foreach ($line_items as $lineItemIndex => $line_item) {
                        $checkoutData = [];
                        $mappedFields = $shopifyFields->getProductFieldlMap();
                        $productData = $shopifyMapFields->ShopifyMapFields($line_item, $mappedFields);
                        $checkoutData = $productData;
                        $checkoutData['discountprice'] = '0.0';
                        $checkoutData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$checkoutData['name']));

                        if ($line_item['variant_title'] == ''){
                            $checkoutData['name']  = $checkoutData['name'];
                        }
                        else {
                            $checkoutData['name']  = $checkoutData['name'].' ('.$line_item['variant_title'].')';
                        }

                        $checkoutData['currency'] = $dataFieldsCart['cartcurrency'];
                        $checkoutData['productid'] = $line_item['variant_id'];
                        $checkoutData['customer'] = $dataFieldsCart['customerinfo']["email"];
                        $removCartItem['productid'] = $line_item['variant_id'];
                        $checkoutData['domain'] = $dataFields['domain'];
                        $checkoutData['cartid'] = $dataFields['cartid'];

                        $productDataFieldsExtra = $this->getProductDetails($checkoutData['productid'],$line_item['product_id'],$shopUrl);
                        $checkoutData['image'] = $productDataFieldsExtra['image'];
                        $checkoutData['categoryid'] = $productDataFieldsExtra['category'];
                        $checkoutData['category'] = $productDataFieldsExtra['category'];
                        $sendData->CartItem($checkoutData, $action);
                    }
                    }
                    catch (\Exception $ex) {
                        DB::table('logging')->insert(
                            [
                                'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                'step' => 0,
                                'comment' => 'checkouts/create error log'
                            ]
                        );
                    }
                }
            break;
            case 'checkouts/update':
                if($settings->abandoned_carts == 1) {

                    $mappedFields = $shopifyFields->getCheckoutFiedlMap();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFields = $shopifyFields->getCustomerFieldMap();
                    $dataFields ['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $action = 1;
                    $dataFields['domain'] = $domain;

                    $mappedFieldsCreateCart = $shopifyFields->getCartBasicFieldMap();
                    $dataFieldsCart = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFieldsCreateCart);

                    $dataFieldsCart['customerinfo'] = $dataFields['customerinfo'];
                    $dataFieldsCart['domain'] = $dataFields['domain'];
                    $dataFieldsCart['customer'] = $dataFieldsCart['customerinfo']["email"];
                    $dataFieldsCart['storename'] = $request->input('line_items')[0]['vendor'];

                    $sendData->Cart($dataFieldsCart, $action);

                    $line_items = $request->input('line_items');

                    $removCartItem['domain'] = $dataFields['domain'];
                    $removCartItem['cartid'] = $dataFields['cartid'];
                    $sendData->CartItem($removCartItem, 3);

                    foreach ($line_items as $lineItemIndex => $line_item) {
                        $checkoutData = [];
                        $mappedFields = $shopifyFields->getProductFieldlMap();
                        $productData = $shopifyMapFields->ShopifyMapFields($line_item, $mappedFields);
                        $checkoutData = $productData;
                        $checkoutData['discountprice'] = '0.0';
                        $checkoutData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$checkoutData['name']));
                        if ( $line_item['variant_title'] == ''){
                            $checkoutData['name']  = $checkoutData['name'];
                        }
                        else $checkoutData['name']  = $checkoutData['name'].' ('.$line_item['variant_title'].')';

                        $checkoutData['currency'] = $dataFieldsCart['cartcurrency'];
                        $checkoutData['productid'] = $line_item['variant_id'];
                        $checkoutData['customer'] = $dataFieldsCart['customerinfo']["email"];
                        $checkoutData['domain'] = $dataFields['domain'];
                        $checkoutData['cartid'] = $dataFields['cartid'];

                        $productDataFieldsExtra = $this->getProductDetails($checkoutData['productid'],$line_item['product_id'],$shopUrl);
                        $checkoutData['image'] = $productDataFieldsExtra['image'];
                        $checkoutData['categoryid'] = $productDataFieldsExtra['category'];

                        $checkoutData['category'] = $productDataFieldsExtra['category'];

                        $sendData->CartItem($checkoutData, 1);
                    }
                }
                break;

            case 'orders/create':
                $mappedFields = $shopifyFields->getOrderFieldMap($shopUrl);
                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getCustomerFieldMap();
                $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getAddressMapFields(1);
                $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getAddressMapFields(2);
                $dataFields['shippingInfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $action = 1;
                $dataFields['domain'] = $domain;
                $dataFields['orderdate'] = strtotime($dataFields['orderdate']);
                $dataFields['storename'] = $request->all()['line_items'][0]['vendor'];
                $sendData->Order($dataFields,$action);
                break;

            case 'orders/updated':
            case 'orders/cancelled':
            case 'orders/fulfilled':
            case 'orders/paid':
                $mappedFields = $shopifyFields->getOrderFieldMap($shopUrl);
                $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getCustomerFieldMap();
                $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getAddressMapFields(1);
                $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $mappedFields = $shopifyFields->getAddressMapFields(2);
                $dataFields['shippingInfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                $action = 2;
                $dataFields['domain'] = $domain;
                $dataFields['orderdate'] = strtotime($dataFields['orderdate']);
                $dataFields['storename'] = $request->all()['line_items'][0]['vendor'];
                $sendData->Order($dataFields,$action);
                break;

            case 'products/create':
                if($settings->product_feed == 1 )
                {
                    $mappedFields = $shopifyFields->getProductMapField();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                    $action = 1;
                    $dataFields['domain'] = $domain;
                    $variants = $request->all()['variants'];
                    $images   = $request->all()['images'];
                    $dataFields['ipaddress'] =  $_SERVER['REMOTE_ADDR'];
                    $dataFields['discountprice'] = '0.0';
                    $productData = $dataFields;
                    foreach ($variants as $ItemIndex => $item)
                    {
                        $productData['sku']         = $item['sku'];
                        $productData['productid']   = $item['id'];
                        $productData['price']       = $item['price'];
                        $productData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$dataFields['name']));
                        if ( $item['title'] == 'Default Title')
                            $productData['name']  = $dataFields['name'];
                        else $productData['name']  = $dataFields['name'].' ('.$item['title'].')';
                        if (isset($item['image_id']) || ($item['image_id'] != null))
                        {
                            foreach ($images as $imageIndex => $imageValue)
                            {
                                if( $item['image_id'] == $imageValue['id'])
                                {
                                    $productData['image'] = $imageValue['src'];
                                    break;
                                }
                            }
                        }
                        else
                        {
                            if ((isset($request->all()['image']['src']) || ($request->all()['image']['src'] != null)))
                                $productData['image'] = $request->all()['image']['src'];
                        }
                        $sendData->Product($productData,$action);
                    }
                }
                break;
            case 'products/update':
                if($settings->product_feed == 1 ) {
                    $mappedFields = $shopifyFields->getProductMapField();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                    $action = 1;
                    $dataFields['domain'] = $domain;
                    $variants = $request->all()['variants'];
                    $images   = $request->all()['images'];
                    $dataFields['ipaddress'] =  $_SERVER['REMOTE_ADDR'];
                    $dataFields['discountprice'] = '0.0';
                    $productData = $dataFields;
                    foreach ($variants as $ItemIndex => $item)
                    {
                        $productData['sku']         = $item['sku'];
                        $productData['productid']   = $item['id'];
                        $productData['price']       = $item['price'];
                        $productData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$dataFields['name']));
                        if ( $item['title'] == 'Default Title')
                            $productData['name']  = $dataFields['name'];
                        else $productData['name']  = $dataFields['name'].' ('.$item['title'].')';
                        if (isset($item['image_id']) || ($item['image_id'] != null))
                        {
                            foreach ($images as $imageIndex => $imageValue)
                            {
                                 if( $item['image_id'] == $imageValue['id'])
                                     {
                                         $productData['image'] = $imageValue['src'];
                                         break;
                                 }
                            }
                        }
                        else
                        {
                            if ((isset($request->all()['image']['src']) || ($request->all()['image']['src'] != null)))
                                $productData['image'] = $request->all()['image']['src'];
                        }
                        $sendData->Product($productData,$action);
                    }
                }
                break;

            default:
                $type = 0;
                break;
        }
        return $mappedFields;
    }

    /**
     * Maps the parameter and sends them to a global function
     * @int  $type
     * @param  $data
     * @string  $shop_url
     * @int  $listId
     * @returns string  $result
     */
    private function mappingAPI($type, $data, $shop_id, $listId, $spiKey)
    {
        $activeSettings = new Setting();
        $activeSettings = $activeSettings->getListActiveSettings($shop_id,'Shopify');

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

    private function loadShop($shopUrl){

        $settings = Shop::where('shop_url', $shopUrl)->first();

        return $settings;
    }

    private function loadSettings($shop){

        $settings = Setting::where('shop_id', $shop)->first();

        return $settings;
    }

    private function getProductDetails($variantId,$productId,$shopUrl){
        $products = (new Products($shopUrl))->product($productId);
        $product = json_decode(json_encode($products, true), true);
        $product = $product['products'][0];
        $variants = $product['variants'];
        $images   = $product['images'];
        $productData['category'] = $product['product_type'];
        $productData['image'] = '';
        foreach ($variants as $ItemIndex => $item)
        {
            if ($item['id'] == $variantId)
            {
                if (isset($item['image_id']) || ($item['image_id'] != null))
                {
                    foreach ($images as $imageIndex => $imageValue)
                    {
                        if( $item['image_id'] == $imageValue['id'])
                        {
                            $productData['image'] = $imageValue['src'];
                            break;
                        }
                    }
                }
                else
                {
                    if ((isset($product['image']['src']) || ($product['image']['src'] != null)))
                        $productData['image'] = $product['image']['src'];
                }
              }
        }
        return $productData;
    }
}
