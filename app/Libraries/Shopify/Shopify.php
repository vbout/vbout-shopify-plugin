<?php

namespace App\Libraries\Shopify;

use App\Models\Shop;
use DB;
class Shopify
{
    protected $token;
    protected $shop;
    protected $apiProtocol;

    public function __construct()
    {
        $this->apiProtocol = 'https://';
    }

    public function sendRequest($apiEndpoint, $method = 'GET', $jsonBody = [])
    {
        try {
            if (!$this->shop) {
                throw new \Exception('Shop not found');
            }
            
            $this->setProtocol();
            $this->setToken();
            
            if (!$this->token) {
                throw new \Exception('Invalid or missing token');
            }

            $client = new \GuzzleHttp\Client();

            $options = [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->token,
                    'Content-Type' => 'application/json'
                ]
            ];

            if ($jsonBody) {
                $options['json'] = $jsonBody;
            }

            $response = $client->request($method, $this->apiProtocol . $apiEndpoint, $options);

            return json_decode($response->getBody());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            DB::table('logging')->insert(
                [
                    'data' => $e->getMessage(),
                    'step' => 0,
                    'comment' => 'WEbhook'
                ]
            );
            return $this->errorResponse($e->getMessage());
        } catch (\Exception $e) {
            DB::table('logging')->insert(
                [
                    'data' => $e->getMessage(),
                    'step' => 0,
                    'comment' => 'WEbhook'
                ]
            );
            return $this->errorResponse($e->getMessage());
        }
    }

    private function setProtocol()
    {
        $this->apiProtocol = 'https://';
    }

    private function setToken()
    {
        $shop = Shop::where('shop_url', $this->shop)->first();

        if ($shop) {
            $this->token = $shop->token;
        }
    }

    private function errorResponse($message)
    {
        return (object) [
            'status' => 'error',
            'message' => $message
        ];
    }
}