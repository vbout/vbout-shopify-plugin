<?php

namespace App\Libraries\Shopify\Services;

use App\Libraries\Shopify\Shopify;

class Shops extends Shopify
{
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function info()
    {
        $response = $this->sendRequest($this->shop . '/admin/shop.json');
        return $response;
    }
}