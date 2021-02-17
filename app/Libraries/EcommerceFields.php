<?php


namespace App\Libraries;


class EcommerceFields
{
    public function ecommerceFields($type)
    {
        $fields = [];

        switch ($type)
        {
            case 1 :
                $fields = $this->addingCustomer();
                break;
            case 2 :
                $fields = $this ->sendingOrderDetails();
                $fields = array_merge($fields,$this->addingCustomer());
                break;
            case 3 :
            case 4 :
                $ecommercefields = $this ->sendingOrderDetails();
                $fields = array_merge($fields,$this->addingCustomer());
            break;
            case 5 :
                $fields = $this->addingCustomer();
                break;

        }
        return fields;
    }
    private function createSettings()
    {

    }

    private function updatingCustomer()
    {
        $fieldsMap = array(
            'email'         => "customer|email",
            'firstname'     => "customer|first_name",
            'lastname'      => "customer|last_name",
            'email'         => "customer|phone",
            'country'       => "customer|default_address|country",
        );
        return $fields = array (
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'country'
        );
    }

    private function addingCartItem()
    {
        $fieldMap = array(
            'cartid'        => "id",
            'price'         => "price",
            'quantity'      => "quantity",
            'sku'           => "sku",
            'link'          => "url",
            'image'         => "image",
            'title'         => "product_title",
            'description'   => "product_description",
            'category'      => "product_type",
            'variation'     => "variant_title",
        );
        return $fields = array (
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
    }
    private function sendingCartDetails()
    {
        return $fields = array (
            'cartid',
            'storename',
            'abandonurl',
            'customer',
            'customerinfo',
            'uniqueid',
        );
    }
    private function clearingCartDetails()
    {
        return $fields = array (
            'cartid',
            'productid',
            'variation',
        );
    }

    private function removingCartItem()
    {
        return $fields = array (
            'cartid',
            'productid',
            'variation',
        );
    }

    private function sendingOrderDetails()
    {
        $customer = [];
        $billingInfo = [];
        $shippingInfo = [];
        $fieldsMap = array(
            'orderid'       => "id",
            'ordernumber'   => "number",
            'orderdate'     => "closed_at",
            'paymentmethod' => "gateway",
            'shippingmethod'=> "fulfillments|tracking_company",
            'shippingcost'  => "total_line_items_price_set|total_shipping_price_set|amount",
            'storename'     => "line_items|vendor",
            'grandtotal'    => "total_price",
            'subtotal'      => "subtotal_price",
            'discountcode'  => "discount_codes",
            'discountvalue' => "total_discounts",
            'taxname'       =>'',
            'taxcost'       => "total_tax",
            'currency'      => "currency",
            'status'        => "fulfillment_status",
            'notes'         => "note",
            'customerinfo'  => $customer,
            'billinginfo'   => $billingInfo,
            'shippinginfo'  => $shippingInfo,

        );
       return $fields = array(
            'cartid',
            'orderid',
            'ordernumber',
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
    }
    private function mapAddress($address)
    {
        $fieldsMap = array(
            'firstname' => 'first_name',
            'lastname' => 'last_name',
            'phone' => 'phone',
            'company' => 'company',
            'address' => 'address1',
            'address2' => 'address2',
            'city' => 'city',
            'statename' => 'province',
            'statecode' => 'province_code',
            'countryname' => 'country',
            'countrycode' => 'country_code',
            'zipcode' => 'zip'
        );
        $fields = array(
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
    }
    private function clearingOrderDetails()
    {
        return $fields = array(
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'country',

        );
    }
    private function addingProductSearch()
    {
        return $fields = array(
            'query',
            'customer',
            'uniqueid',
        );
    }
    private function addingProductView()
    {
        return $fields = array(
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
    }

    private function addingCategoryView()
    {
        return $fields = array(
            'customer',
            'uniqueid',
            'categoryid',
            'name',
            'link',
            'image',
            'description',
        );
    }

    public function configurationSettingList()
    {
        return $fields  = array(
            'Abandonded Carts',
            'Search',
            'Product Visit',
            'Category Visits',
            'New Customers',
            'Product Feed',
            'Current Customers',
            'Marketing'
        );
    }
    private function addingCustomer()
    {
        $fieldsMap = array(
            'email'         => "email",
            'firstname'     => "first_name",
            'lastname'      => "last_name",
            'email'         => "phone",
            'country'       => "country",
        );
        return $fields = array(
            'firstname',
            'lastname',
            'email',
            'phone',
            'company',
            'country',
        );
    }
}