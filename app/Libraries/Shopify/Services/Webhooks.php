<?php

namespace App\Libraries\Shopify\Services;

use App\Libraries\Shopify\Shopify;

class Webhooks extends Shopify
{
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    public function create($topic, $address, $format = 'json')
    {
        $jsonBody = [
            'webhook' => [
                'topic' => $topic,
                'address' => $address,
                'format' => $format
            ]
        ];
        $response = $this->sendRequest($this->shop . '/admin/webhooks.json', 'POST', $jsonBody);
        return $response;
    }

    public function info($id)
    {
        $response = $this->sendRequest($this->shop . '/admin/customers/' . $id . '.json');
        return $response;
    }

    public function all()
    {
        $response = $this->sendRequest($this->shop . '/admin/webhooks.json');
        return $response;
    }

    public function delete($id)
    {
        $response = $this->sendRequest($this->shop . '/admin/webhooks/' . $id . '.json', 'DELETE');
        return $response;
    }
}