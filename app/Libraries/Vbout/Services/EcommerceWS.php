<?php


namespace App\Libraries\Vbout\Services;

use App\Libraries\Vbout\Vbout;
use App\Libraries\Vbout\VboutException;
use App\Models\Setting;
use DB;

class EcommerceWS extends Vbout
{
    protected function init(){
        $this->api_url = '/ecommerce/';
    }

    public function sendEcommerce($type, $params = array())
    {
        {
            $result = array();

            try {
                $this->set_method('POST');

                $insertRecord = $this->insertAPI($type, $params);

                if ($insertRecord != null && isset($insertRecord['data'])) {
                    $result = $insertRecord['data']['item'];
                }
            } catch (VboutException $ex) {
                $result = $ex->getData();
            }

            return $result;
        }
    }

    public function Customer($data, $action)
    {
        $result = array();
        try {
            $this->set_method('POST');
            if ($action == 1 )
                $insertRecord = $this->upsertCustomer($data);
            else if($action == 2)
                $insertRecord = $this->upsertCustomer($data);

            else $result = "Error with Action taken.";

            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];
            }

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Customer API Log: '.time()
                ]
            );

        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Customer API Error Log: '.time()
                ]
            );
        }
        return $result;

    }

    public function Cart($data, $action)
    {
        $result = array();
        try {
            $this->set_method('POST');
            if ($action == 1 )
                $insertRecord = $this->CreateCart($data);
            if ($action == 2 )
                $insertRecord = $this->UpdateCart($data);
            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];
            }

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Cart API Log: '.time()
                ]
            );
        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Cart API Error Log: '.time()
                ]
            );
        }
        return $result;
    }

    public function CartItem($data, $action)
    {
        $result = array();

        try {
            $this->set_method('POST');
            if ($action == 1){
                $insertRecord = $this->AddCartItem($data);
            }
            else if($action == 2 ){
                $insertRecord = $this->CreateCart($data);
            }
            else {
                $insertRecord = $this->EmptyCart($data);
            }

            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];
            }

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Cart Item API Log: '.time()
                ]
            );
        }

        catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Cart Item API Error Log: '.time()
                ]
            );
        }
        return $result;
    }

    public function Order($data, $action)
    {

        $result = array();
        try {
            $this->set_method('POST');
            if ($action == 1 )
                $insertRecord = $this->createOrder($data);
            if ($action == 2 )
                $insertRecord = $this->updateOrder($data);
            else $result = "Error";

            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];
            }

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Order API Log: '.time()
                ]
            );

        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Order API Error Log: '.time()
                ]
            );
        }
        return $result;
    }

    public function Product($data, $action)
    {
        $result = array();
        try {
            $this->set_method('POST');
            if ($action == 1 )
                $insertRecord = $this->addProductView($data);
            else $result = "Error";

            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];
            }

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Product API Log: '.time()
                ]
            );

        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Product API Error Log: '.time()
                ]
            );
        }
        return $result;
    }

    public function getSettingsSync()
    {
        $result = array();
        try {
            $settings = $this->settingsSync();

            if ($settings != null && isset($settings['data'])) {
                $result = array_merge($result, $settings['data']['$settings']);

                //update Settings on API side
                $settingUpdate = Setting::find($result['shop_id']);
                $settingUpdate->abandoned_carts = $result['abandoned_carts'];
                $settingUpdate->search = $result['search'];
                $settingUpdate->product_visits = $result['product_visits'];
                $settingUpdate->category_visits = $result['category_visits'];
                $settingUpdate->customers = $result['customers'];
                $settingUpdate->product_feed = $result['product_feed'];
                $settingUpdate->current_customers = $result['current_customers'];
                $settingUpdate->marketing = $result['marketing'];
                $settingUpdate->save();
            }

            DB::table('logging')->insert(
                [
                    'data' => 'Get Settings Successfully',
                    'step' => 0,
                    'comment' => 'Settings Sync API Error Log'
                ]
            );

        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Settings Sync API Error Log'
                ]
            );
        }

        return $result;
    }

    public function sendSettingsSync($data)
    {
        $result = array();
        try {
            $this->set_method('POST');
            $insertRecord = $this->updateSettings($data);

            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];

            }

            DB::table('logging')->insert(
                [
                    'data' => 'Send Settings Successfully',
                    'step' => 0,
                    'comment' => 'Send Settings Sync API Log'
                ]
            );

        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Send Settings Sync API Error Log'
                ]
            );

        }
        return $result;
    }

    public function sendAPIIntegrationCreation($shop,$action =1)
    {
        $data['domain']  = $shop->domain;
        $data['apiname'] = 'Shopify';
        $data['apiKey']  = $shop->apiKey;
        $result = array();
        try {
            $this->set_method('POST');
            if ($action ==1)
                $insertRecord = $this->createIntegration($data);
            else if ($action == 3)
                $insertRecord = $this->removeSettings($data);

            if ($insertRecord != null && isset($insertRecord['data'])) {
                $result = $insertRecord['data'];
            }

            DB::table('logging')->insert(
                [
                    'data' => 'Integration Created Successfully',
                    'step' => 0,
                    'comment' => 'Send Integration API Log'
                ]
            );

        } catch (VboutException $ex) {
            $result = $ex->getData();

            DB::table('logging')->insert(
                [
                    'data' => json_encode($result),
                    'step' => 0,
                    'comment' => 'Send Integration API Error Log'
                ]
            );
        }
        return $result;
    }
}