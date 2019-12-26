<?php

namespace App\Libraries\Shopify\Services;

use App\Libraries\Shopify\Shopify;

class Products extends Shopify
{
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function all()
    {
        $response = $this->sendRequest($this->shop . '/admin/api/2019-04/products.json');
        return $response;
    }
    public function Product($productId)
    {
        $response = $this->sendRequest($this->shop . '/admin/api/2019-04/products.json?ids='.$productId);
        return $response;
    }
}