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

    private function getFieldMaps($request){

        $shopifyFields = new ShopfiyFields();
        $shopifyMapFields = new MapFieldsController();

        $event = $request->header('X-Shopify-Topic');
        $shopUrl = $request->header('X-Shopify-Shop-Domain');

        $shop = $this->loadShop($shopUrl);

        $sendData = new EcommerceWS(['api_key' => $shop->apiKey]);
        $settings = $this->loadSettings($shop->id);

        $domain = $shop->domain;

        // 1 for Create, 2 for update , 3 for deletion
        $action = 0 ;

        DB::table('logging')->insert(
            [
                'data' => json_encode($request->all()),
                'step' => $domain,
                'comment' => $event .' init '. time()
            ]
        );

        switch ($event) {
            case 'customers/create':
                if($settings->customers == 1) {
                    try{
                        $mappedFields = $shopifyFields->getCustomerAloneFieldMap();
                        $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                        $action = 1;
                        $dataFields['domain'] = $domain;
                        $sendData->Customer($dataFields,$action);
                    }
                    catch (\Exception $ex) {
                        DB::table('logging')->insert(
                            [
                                'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                'step' => $domain,
                                'comment' => 'customers/create error log'
                            ]
                        );
                    }
                }
                break;
            case 'customers/update':
                if($settings->customers == 1) {
                    sleep(1);

                    try{
                        $mappedFields = $shopifyFields->getCustomerAloneFieldMap();
                        $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);
                        $action = 2;
                        $dataFields['domain'] = $domain;
                        $sendData->Customer($dataFields, $action);
                    }
                    catch (\Exception $ex) {
                        DB::table('logging')->insert(
                            [
                                'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                'step' => $domain,
                                'comment' => 'customers/update error log'
                            ]
                        );
                    }
                }
                break;

            case 'checkouts/create':
                if($settings->abandoned_carts == 1) {
                    sleep(2);

                    try {
                        $mappedFields = $shopifyFields->getCheckoutFieldMap();
                        $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                        $mappedFields = $shopifyFields->getCustomerFieldMap();
                        $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                        $mappedFieldsCreateCart = $shopifyFields->getCartBasicFieldMap();
                        $dataFieldsCart = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFieldsCreateCart);

                        $action = 1;

                        $dataFields['domain'] = $domain;
                        $dataFieldsCart['customerinfo'] = $dataFields['customerinfo'];
                        $dataFieldsCart['domain'] = $dataFields['domain'];
                        $dataFieldsCart['customer'] = isset($dataFieldsCart['customerinfo']['email']) ? $dataFieldsCart['customerinfo']['email'] : '';
                        $dataFieldsCart['storename'] = $request->input('line_items')[0]['vendor'];

                        $sendData->Cart($dataFieldsCart, $action);

                        $line_items = $request->input('line_items');

                        $removeCartItem['domain'] = $dataFields['domain'];
                        $removeCartItem['cartid'] = $dataFields['cartid'];
                        $removeCartItem['carttoken'] = $dataFields['carttoken'];
                        $sendData->CartItem($removeCartItem, 3);
                        sleep(1);

                        foreach ($line_items as $lineItemIndex => $line_item) {
                            $mappedFields = $shopifyFields->getProductFieldMap();
                            $productData = $shopifyMapFields->ShopifyMapFields($line_item, $mappedFields);

                            $checkoutData = $productData;
                            $checkoutData['discountprice'] = '0.0';
                            $checkoutData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$checkoutData['name']));

                            if ($line_item['variant_title'] != ''){
                                $checkoutData['name']  = $checkoutData['name'].' ('.$line_item['variant_title'].')';
                            }

                            $checkoutData['currency'] = $dataFieldsCart['cartcurrency'];
                            $checkoutData['productid'] = $line_item['variant_id'];
                            $checkoutData['customer'] = isset($dataFieldsCart['customerinfo']['email']) ? $dataFieldsCart['customerinfo']['email'] : '';
                            $removeCartItem['productid'] = $line_item['variant_id'];
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
                                'step' => $domain,
                                'comment' => 'checkouts/create error log'
                            ]
                        );
                    }
                }
                break;
            case 'checkouts/update':
                if($settings->abandoned_carts == 1) {
                    sleep(3);

                    try{
                        $mappedFields = $shopifyFields->getCheckoutFieldMap();
                        $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                        $mappedFields = $shopifyFields->getCustomerFieldMap();
                        $dataFields ['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                        $action = 2;
                        $dataFields['domain'] = $domain;

                        $mappedFieldsCreateCart = $shopifyFields->getCartBasicFieldMap();
                        $dataFieldsCart = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFieldsCreateCart);

                        $dataFieldsCart['customerinfo'] = $dataFields['customerinfo'];
                        $dataFieldsCart['domain'] = $dataFields['domain'];
                        $dataFieldsCart['customer'] = isset($dataFieldsCart['customerinfo']['email']) ? $dataFieldsCart['customerinfo']['email'] : '';
                        $dataFieldsCart['storename'] = $request->input('line_items')[0]['vendor'];

                        $sendData->Cart($dataFieldsCart, $action);

                        $line_items = $request->input('line_items');

                        $removeCartItem['domain'] = $dataFields['domain'];
                        $removeCartItem['cartid'] = $dataFields['cartid'];
                        $removeCartItem['carttoken'] = $dataFields['carttoken'];
                        $sendData->CartItem($removeCartItem, 3);
                        sleep(1);

                        foreach ($line_items as $lineItemIndex => $line_item) {
                            $mappedFields = $shopifyFields->getProductFieldMap();
                            $productData = $shopifyMapFields->ShopifyMapFields($line_item, $mappedFields);

                            $checkoutData = $productData;
                            $checkoutData['discountprice'] = '0.0';
                            $checkoutData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$checkoutData['name']));

                            if ($line_item['variant_title'] != ''){
                                $checkoutData['name']  = $checkoutData['name'].' ('.$line_item['variant_title'].')';
                            }

                            $checkoutData['currency'] = $dataFieldsCart['cartcurrency'];
                            $checkoutData['productid'] = $line_item['variant_id'];
                            $removeCartItem['productid'] = $line_item['variant_id'];
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
                    catch (\Exception $ex) {
                        DB::table('logging')->insert([
                            'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                            'step' => $domain,
                            'comment' => 'checkouts/update error log'
                            ]
                        );
                    }
                }
                break;

            case 'orders/create':
                sleep(2);

                try{
                    $mappedFields = $shopifyFields->getOrderFieldMap();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    if(empty($dataFields['cartid'])) {
                        $dataFields['cartid'] = $dataFields['carttoken'];

                        try {
                            $cartDataFields = array();

                            $cartDataFields['domain'] = $domain;
                            $cartDataFields['uniqueid'] = $dataFields['orderid'];
                            $cartDataFields['cartid'] = $dataFields['cartid'];
                            $cartDataFields['cartcurrency'] = $request->input('currency');

                            $sendData->Cart($cartDataFields, 1);

                            $line_items = $request->input('line_items');

                            foreach ($line_items as $lineItemIndex => $line_item) {
                                $mappedFields = $shopifyFields->getProductFieldMap();
                                $productData = $shopifyMapFields->ShopifyMapFields($line_item, $mappedFields);

                                $lineItemData = array();
                                $lineItemData['domain'] = $domain;
                                $lineItemData['cartid'] = $cartDataFields['cartid'];
                                $lineItemData['productid'] = $productData['productid'];
                                $lineItemData['name'] = $productData['price'];
                                $lineItemData['price'] = $productData['price'];
                                $lineItemData['quantity'] = $productData['quantity'];
                                $lineItemData['discountprice'] = '0.0';
                                $lineItemData['link'] = 'https://' . $shopUrl . '/products/' . strtolower(str_replace(" ", "-", $productData['name']));

                                $productDataFieldsExtra = $this->getProductDetails($productData['productid'], $line_item['product_id'], $shopUrl);
                                $lineItemData['image'] = $productDataFieldsExtra['image'];
                                $lineItemData['categoryid'] = $productDataFieldsExtra['category'];
                                $lineItemData['category'] = $productDataFieldsExtra['category'];

                                if ($line_item['variant_title'] != '') {
                                    $lineItemData['name'] = $productData['name'] . ' (' . $line_item['variant_title'] . ')';
                                }

                                $sendData->CartItem($lineItemData, 1);
                            }
                        }
                        catch (\Exception $ex) {
                            DB::table('logging')->insert([
                                    'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                    'step' => $domain,
                                    'comment' => 'Force Cart Creation error log'
                                ]
                            );
                        }
                    }

                    $mappedFields = $shopifyFields->getCustomerFieldMap();
                    $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFields = $shopifyFields->getAddressMapFields(1);
                    $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFields = $shopifyFields->getAddressMapFields(2);
                    $dataFields['shippinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $action = 1;

                    $dataFields['domain'] = $domain;
                    $dataFields['orderdate'] = strtotime($dataFields['orderdate']);
                    $dataFields['storename'] = $request->all()['line_items'][0]['vendor'];

                    $sendData->Order($dataFields,$action);
                }
                catch (\Exception $ex) {
                    DB::table('logging')->insert([
                            'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                            'step' => $domain,
                            'comment' => 'orders/create error log'
                        ]
                    );
                }
                break;

            case 'orders/updated':
            case 'orders/cancelled':
            case 'orders/fulfilled':
            case 'orders/paid':
                sleep(3);

                try{
                    $mappedFields = $shopifyFields->getOrderFieldMap();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    if(empty($dataFields['cartid'])) {
                        $dataFields['cartid'] = $dataFields['carttoken'];

                        try {
                            $cartDataFields = array();

                            $cartDataFields['domain'] = $domain;
                            $cartDataFields['uniqueid'] = $dataFields['orderid'];
                            $cartDataFields['cartid'] = $dataFields['cartid'];
                            $cartDataFields['cartcurrency'] = $request->input('currency');

                            $sendData->Cart($cartDataFields, 1);

                            $line_items = $request->input('line_items');

                            foreach ($line_items as $lineItemIndex => $line_item) {
                                $mappedFields = $shopifyFields->getProductFieldMap();
                                $productData = $shopifyMapFields->ShopifyMapFields($line_item, $mappedFields);

                                $lineItemData = array();
                                $lineItemData['domain'] = $domain;
                                $lineItemData['cartid'] = $cartDataFields['cartid'];
                                $lineItemData['productid'] = $productData['productid'];
                                $lineItemData['name'] = $productData['price'];
                                $lineItemData['price'] = $productData['price'];
                                $lineItemData['quantity'] = $productData['quantity'];
                                $lineItemData['discountprice'] = '0.0';
                                $lineItemData['link'] = 'https://' . $shopUrl . '/products/' . strtolower(str_replace(" ", "-", $productData['name']));

                                $productDataFieldsExtra = $this->getProductDetails($productData['productid'], $line_item['product_id'], $shopUrl);
                                $lineItemData['image'] = $productDataFieldsExtra['image'];
                                $lineItemData['categoryid'] = $productDataFieldsExtra['category'];
                                $lineItemData['category'] = $productDataFieldsExtra['category'];

                                if ($line_item['variant_title'] != '') {
                                    $lineItemData['name'] = $productData['name'] . ' (' . $line_item['variant_title'] . ')';
                                }

                                $sendData->CartItem($lineItemData, 1);
                            }
                        } catch (\Exception $ex) {
                            DB::table('logging')->insert([
                                    'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                    'step' => $domain,
                                    'comment' => 'Force Cart Creation error log'
                                ]
                            );
                        }
                    }

                    $mappedFields = $shopifyFields->getCustomerFieldMap();
                    $dataFields['customerinfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFields = $shopifyFields->getAddressMapFields(1);
                    $dataFields['billinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $mappedFields = $shopifyFields->getAddressMapFields(2);
                    $dataFields['shippinginfo'] = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                    $action = 2;

                    $dataFields['domain'] = $domain;
                    $dataFields['orderdate'] = strtotime($dataFields['orderdate']);
                    $dataFields['storename'] = $request->all()['line_items'][0]['vendor'];
                    $sendData->Order($dataFields,$action);
                }
                catch (\Exception $ex) {
                    DB::table('logging')->insert([
                            'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                            'step' => $domain,
                            'comment' => 'orders/updated error log'
                        ]
                    );
                }

                break;

            case 'products/create':
                if($settings->product_feed == 1 ) {
                    try{
                        $mappedFields = $shopifyFields->getProductMapField();
                        $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                        $action = 1;

                        $dataFields['domain'] = $domain;

                        $variants = $request->all()['variants'];
                        $images   = $request->all()['images'];

                        $dataFields['discountprice'] = '0.0';

                        $productData = $dataFields;

                        foreach ($variants as $ItemIndex => $item) {
                            $productData['sku']         = $item['sku'];
                            $productData['productid']   = $item['id'];
                            $productData['price']       = $item['price'];
                            $productData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$dataFields['name']));

                            if ($item['title'] == 'Default Title'){
                                $productData['name']  = $dataFields['name'];
                            }
                            else{
                                $productData['name']  = $dataFields['name'].' ('.$item['title'].')';
                            }

                            if (isset($item['image_id']) || ($item['image_id'] != null)) {
                                foreach ($images as $imageIndex => $imageValue)
                                {
                                    if( $item['image_id'] == $imageValue['id'])
                                    {
                                        $productData['image'] = $imageValue['src'];
                                        break;
                                    }
                                }
                            }
                            else {
                                if ((isset($request->all()['image']['src']) || ($request->all()['image']['src'] != null)))
                                    $productData['image'] = $request->all()['image']['src'];
                            }

                            $sendData->Product($productData,$action);
                        }
                    }
                    catch (\Exception $ex) {
                        DB::table('logging')->insert([
                                'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                'step' => $domain,
                                'comment' => 'products/create error log'
                            ]
                        );
                    }
                }
                break;
            case 'products/update':
                if($settings->product_feed == 1 ) {
                    try{
                        $mappedFields = $shopifyFields->getProductMapField();
                        $dataFields = $shopifyMapFields->ShopifyMapFields($request->all(), $mappedFields);

                        $action = 1;

                        $dataFields['domain'] = $domain;

                        $variants = $request->all()['variants'];
                        $images   = $request->all()['images'];

                        $dataFields['discountprice'] = '0.0';

                        $productData = $dataFields;

                        foreach ($variants as $ItemIndex => $item) {
                            $productData['sku']         = $item['sku'];
                            $productData['productid']   = $item['id'];
                            $productData['price']       = $item['price'];
                            $productData['link'] = 'https://'.$shopUrl.'/products/'.strtolower(str_replace(" ","-",$dataFields['name']));

                            if ($item['title'] == 'Default Title'){
                                $productData['name']  = $dataFields['name'];
                            }
                            else{
                                $productData['name']  = $dataFields['name'].' ('.$item['title'].')';
                            }

                            if (isset($item['image_id']) || ($item['image_id'] != null)) {
                                foreach ($images as $imageIndex => $imageValue) {
                                    if( $item['image_id'] == $imageValue['id']) {
                                        $productData['image'] = $imageValue['src'];
                                        break;
                                    }
                                }
                            }
                            else {
                                if ((isset($request->all()['image']['src']) || ($request->all()['image']['src'] != null)))
                                    $productData['image'] = $request->all()['image']['src'];
                            }

                            $sendData->Product($productData,$action);
                        }
                    }
                    catch (\Exception $ex) {
                        DB::table('logging')->insert([
                                'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                                'step' => $domain,
                                'comment' => 'products/update error log'
                            ]
                        );
                    }
                }
                break;

            default:
                break;
        }

        return true;
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
        foreach ($variants as $ItemIndex => $item) {
            if ($item['id'] == $variantId) {
                if (isset($item['image_id']) || ($item['image_id'] != null)) {
                    foreach ($images as $imageIndex => $imageValue) {
                        if( $item['image_id'] == $imageValue['id']) {
                            $productData['image'] = $imageValue['src'];
                            break;
                        }
                    }
                }
                else {
                    if(isset($product['image'])){
                        if (!empty($product['image']['src'])){
                            $productData['image'] = $product['image']['src'];
                        }
                    }
                }
            }
        }
        return $productData;
    }

}
