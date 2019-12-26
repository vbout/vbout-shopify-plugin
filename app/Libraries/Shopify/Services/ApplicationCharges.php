<?php

namespace App\Libraries\Shopify\Services;

use App\Libraries\Shopify\Shopify;

class ApplicationCharges extends Shopify
{
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function create($name, $price, $returnUrl, $isTest = false)
    {
        $params = [
            'application_charge' => [
                'name' => $name,
                'price' => $price,
                'return_url' => $returnUrl,
                'test' => $isTest ? true : null
            ]
        ];

        $response = $this->sendRequest($this->shop . '/admin/application_charges.json', 'POST', $params);

        return $response;
    }

    public function all()
    {
        $response = $this->sendRequest($this->shop . '/admin/application_charges.json');

        return $response;
    }

    public function activate($id)
    {
        $getChargeResponse = $this->sendRequest($this->shop . '/admin/application_charges/' . $id . '.json');
        $status = 'declined';

        if (isset($getChargeResponse->application_charge)) {
            if ($getChargeResponse->application_charge->status === 'accepted') {
                $response = $this->sendRequest($this->shop . '/admin/application_charges/' . $id . '/activate.json', 'POST', (array) $getChargeResponse);
                $status = $response->application_charge->status;
            }
        }

        return $status;
    }
}