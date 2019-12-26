<?php

namespace App\Libraries\Shopify\Services;

use App\Libraries\Shopify\Shopify;

class Customers extends Shopify
{
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function all()
    {
        $response = $this->sendRequest($this->shop . '/admin/customers.json');
        return $response;
    }
}