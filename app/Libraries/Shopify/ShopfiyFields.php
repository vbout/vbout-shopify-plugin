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
        $Vboutfields = array (
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'country'
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

        $Vboutfields = array (
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'country'
        );
        return $fieldsMap;
    }


    public function getCartFieldMap()
    {
        $fieldMap = array(
            'cartid'        => "id",
            'uniqueid'      => "token",
           );
        $Vboutfields = array (
            'cartid',
            'productid',
            'name',
            'description',
            'variation',
            'price',
            'discountprice',
            'currency',
            'quantity',
            'sku',
            'categoryid',
            'category',
            'link',
            'image',
        );
        return $fieldMap;
    }
    public function getCartBasicFieldMap()
    {
        $fieldMap = array(
            'cartid'        => "cart_token",
            'uniqueid'      => "id",
            'customer'      => "email",
            'cartcurrency'  => "presentment_currency",
            'abandonurl'    => "abandoned_checkout_url"
        );

        return $fieldMap;
    }
    public function getOrderFieldMap($storename)
    {
        $fieldsMap = array(
            'orderid'       => "id",
            'orderdate'     => "created_at",
            'paymentmethod' => "gateway",
            'shippingmethod'=> "shipping_lines|source",
            'shippingcost'  => "total_shipping_price_set|shop_money|amount",
            'grandtotal'    => "total_price",
            'subtotal'      => "subtotal_price",
            'discountcode'  => "discount_codes|code",
            'discountvalue' => "total_discounts",
//            'taxname'       => 'tax_lines|title',
            'taxcost'       => "total_tax",
//            'storename'      => $storename,
            'currency'      => "currency",
            'status'        => "financial_status",
            'notes'         => "note",
            'cartid'        => "cart_token",
            'ipaddress'     => 'browser_ip'
        );
        $Vboutfields  = array(
            'cartid',
            'orderid',
            'orderdate',
            'paymentmethod',
            'shippingmethod',
            'shippingcost',
            'storename',
            'grandtotal',
            'subtotal',
            'promocode',
            'promovalue',
            'discountcode',
            'discountvalue',
            'taxname',
            'taxcost',
            'otherfeename',
            'otherfeecost',
            'currency',
            'status',
            'notes',
            'customerinfo',
            'billinginfo',
            'shippinginfo',

        );
        return $fieldsMap;
    }
    public function getCheckoutFiedlMap()
    {
        $fieldMap = array(
            'cartid'        => "cart_token",
            'uniqueid'      => "id",
            'cartcurrency'  => "presentment_currency",
            'abandonurl'    => "abandoned_checkout_url",
        );
        return $fieldMap;
    }
    public function getProductFieldlMap()
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
    public function getCartitemFieldlMap()
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
        if ($type = 1)
            $type = 'billing_address';
        else if ($type = 2)
            $type = 'shipping_address';

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
        $Vboutfields  = array(
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'address',
            'address2',
            'city',
            'statename',
            'statecode',
            'countryname',
            'countrycode',
            'zipcode',

        );
        return $fieldsMap;
    }
    public function getProductFeedFieldlMap()
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

    public function getProductFeedVariantFieldlMap()
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
        //There's no way to determine what a visitor or customer is looking at with the Shopify API.
        $fieldsMap = array(
            'productid'     => 'id',
            'name'          => 'title',
            'categoryid'    => 'product_type',
            'category'      => 'product_type',
            'description'   => 'body_html',
//            'image'         => 'image|src'
        );

        $Vboutfields  = array(
            'customer',
            'uniqueid',
            'productid',
            'name',
            'price',
            'discountprice',
            'currency',
            'sku',
            'categoryid',
            'category',
            'link',
            'image',
            'description',

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
        //Also Not provided or available by Shopify
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