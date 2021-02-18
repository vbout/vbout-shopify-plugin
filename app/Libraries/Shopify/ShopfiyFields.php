<?php


namespace App\Libraries\Shopify;
use DB;

use phpDocumentor\Reflection\UsingTagsTest;

class ShopfiyFields
{
    public function getCustomerFieldMap()
    {
        $fieldsMap = array(
            'email'         => "customer|email",
            'firstname'     => "customer|first_name",
            'lastname'      => "customer|last_name",
            'phone'         => "customer|phone",
            'country'       => "customer|default_address|country",
        );
        return $fieldsMap;
    }

    public function getCustomerAloneFieldMap()
    {
        $fieldsMap = array(
            'email'         => "email",
            'firstname'     => "first_name",
            'lastname'      => "last_name",
            'phone'         => "phone",
            'country'       => "default_address|country",
        );
        return $fieldsMap;
    }

    public function getCartFieldMap()
    {
        $fieldMap = array(
            'cartid'        => "id",
            'uniqueid'      => "token",
           );

        return $fieldMap;
    }

    public function getCartBasicFieldMap()
    {
        $fieldMap = array(
            'cartid'        => "cart_token",
            'carttoken'     => "token",
            'uniqueid'      => "id",
            'customer'      => "email",
            'cartcurrency'  => "presentment_currency",
            'abandonurl'    => "abandoned_checkout_url"
        );
        return $fieldMap;
    }

    public function getOrderFieldMap()
    {
        $fieldsMap = array(
            'orderid'       => "id",
            'ordernumber'   => "number",
            'orderdate'     => "created_at",
            'paymentmethod' => "gateway",
            'shippingmethod'=> "shipping_lines|source",
            'shippingcost'  => "total_shipping_price_set|shop_money|amount",
            'grandtotal'    => "total_price",
            'subtotal'      => "subtotal_price",
            'discountcode'  => "discount_codes|code",
            'discountvalue' => "total_discounts",
            'taxcost'       => "total_tax",
            'currency'      => "currency",
            'status'        => "financial_status",
            'notes'         => "note",
            'cartid'        => "cart_token",
            'carttoken'     => "token",
            'ipaddress'     => 'browser_ip'
        );
        return $fieldsMap;
    }

    public function getCheckoutFieldMap()
    {
        $fieldMap = array(
            'cartid'        => "cart_token",
            'carttoken'     => "token",
            'uniqueid'      => "id",
            'cartcurrency'  => "presentment_currency",
            'abandonurl'    => "abandoned_checkout_url",
        );
        return $fieldMap;
    }

    public function getProductFieldMap()
    {
        $fieldMap = array(
            'quantity'      => "quantity",
            'productid'     => "product_id",
            'price'         => "price",
            'name'          => "title",
            'sku'           => "sku",
//            'variation'     => "variant_title",
//            'discountprice' => "applied_discounts",
        );
        return $fieldMap;
    }

    public function getCartItemFieldMap()
    {
        $fieldMap = array(
            'quantity'      => "quantity",
            'productid'     => "id",
            'price'         => "price",
            'name'          => "title",
            'sku'           => "sku",
//            'variation'     => "variant_title",
            'discountprice' => "discounted_price",
            'currency'  => "line_price_set|presentment_money|currency_code",

        );
        return $fieldMap;
    }

    public function getAddressMapFields($type)
    {
        if ($type == 1){
            $type = 'billing_address';
        }
        else if ($type == 2){
            $type = 'shipping_address';
        }

        $fieldsMap = array(
            'firstname'     =>  $type.'|first_name',
            'lastname'      =>  $type.'|last_name',
            'phone'         =>  $type.'|phone',
            'company'       =>  $type.'|company',
            'address'       =>  $type.'|address1',
            'address2'      =>  $type.'|address2',
            'city'          =>  $type.'|city',
            'statename'     =>  $type.'|province',
            'statecode'     =>  $type.'|province_code',
            'countryname'   =>  $type.'|country',
            'countrycode'   =>  $type.'|country_code',
            'zipcode'       =>  $type.'|zip'
        );
        return $fieldsMap;
    }

    public function getProductFeedFieldMap()
    {

        $fieldMap = array(
            'productid'		=>'id',
            'name'		    =>'title',
            'category'		=>'product_type',
            'image'		    =>'image',
//            'description'	=>'xxxxxxxxxxxxxxxxxx',
        );
        return $fieldMap;
    }

    public function getProductFeedVariantFieldMap()
    {

        $fieldMap = array(
            'price'		    =>'variants|price',
            'discountprice'	=>'variants|sku',
            'currency'		=>'variants|presentment_prices|price|currency_code',
            'sku'		    =>'variants|sku',
//            'link'		    =>'xxxxxxxxxxxxxxxxxx',
         );
        return $fieldMap;
    }

    public function getProductMapField()
    {
        $fieldsMap = array(
            'productid'     => 'id',
            'name'          => 'title',
            'categoryid'    => 'product_type',
            'category'      => 'product_type',
            'description'   => 'body_html',
//            'image'         => 'image|src'
        );
        return $fieldsMap;
    }

    private function getCategoryMapField($data)
    {
        //Also Not provided or available by Shopify
        $Vboutfields  = array(
            'customer',
            'uniqueid',
            'categoryid',
            'name',
            'link',
            'image',
            'description',
        );
        return $Vboutfields;

    }

    public function getSettingsMapField()
    {
        $Vboutfields  = array(
            'abandoned_carts'       =>  'Abandoned carts (When a checkout/order is created or updated on Shopify.) ',
            'product_visits'        =>  'Product Visits ( This feature is not available )',
            'category_visits'       =>  'Category Visits ( This feature is not available )' ,
            'customers'             =>  'Customer data (When customer profiles are added or updated on Shopify.)',
            'current_customers'     =>  'Existing Customers (Syncs all your current customer data before installing the plugin.)',
            'product_feed'          =>  'Product data (When products are added or updated on Shopify.)',
            'sync_current_products' =>  'Existing products (Syncs all your products before installing the plugin.)',
            'marketing'             =>  'Marketing ',
            'search'                =>  'Search (Customer Search) ',
        );
        return $Vboutfields;
    }

}