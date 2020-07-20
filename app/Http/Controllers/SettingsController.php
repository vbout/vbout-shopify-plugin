<?php

namespace App\Http\Controllers;

use App\Libraries\Shopify\Services\Products;
use App\Libraries\Shopify\ShopfiyFields;
use App\Libraries\Vbout\Services\ApplicationWS;
use App\Libraries\Vbout\Services\EcommerceWS;
use App\Models\Domains;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Setting;
use App\Libraries\Vboutify;
use App\Libraries\Vbout\Services\EmailMarketingWS;
use App\Libraries\Shopify\Services\Customers;
use DB;

class SettingsController extends Controller{

    public function edit(Request $request, $shopUrl)
    {
		$settings = null;
		$isTrial = true;
		$isSetupComplete = false;
		$daysLeft = 0;
		
		$APP_URL = env('APP_URL');
		$upgradeUrl = $APP_URL.'/public/index.php/app?shop='.$shopUrl.'&upgradeplan=true';
			
		$shop = Shop::where('shop_url', $shopUrl)->first();
		if ($shop->domain == '' )
        {
            $shop = Shop::where('shop_url', $shopUrl)->first();
            if ($shop->domain == '' )
            {
                $shoUrlArray = explode('.',$shopUrl);
                // check the shop url having the domain in it
                $domain = Domains::where('shop_url','like',$shoUrlArray[0].'.%')->orderBy('id', 'DESC')->pluck('domain');
                if(count($domain) != 0)
                {
                    $shop->domain = $domain[0];
                    $shop->save();
                }
                else
                    return view('errorPage', [
                        'message' => 'Your website\'s Domain name is different from the one used with Shopify.',
                        'urlRedirect' => 'app.vbout.com/Settings/Integrations'
                    ]);

            }            $shop->domain = $domain[0];
            $shop->save();
        }

		if($shop){				
			$settings = json_decode($shop->settings);
			$isTrial = $shop->trial;
			$isSetupComplete = $shop->setup_complete;
			
			$created_at = $shop->created_at;			
			$today_date = time();
			$created_date = strtotime($created_at);
			$datediff = $today_date - $created_date;
			$daysPassed = floor($datediff / (60 * 60 * 24));
			$trialdays = config('app.shopify_app_trial_days');
			$daysLeft =  floor($trialdays - $daysPassed);
		}
		
        $shopifyFields = explode(',', env('SHOPIFY_APP_FIELDS'));
        $shopifyFieldsPurchase = explode(',', env('SHOPIFY_APP_FIELDS_PURCHASE'));
        $shopifyFieldsIncomplete = explode(',', env('SHOPIFY_APP_FIELDS_INCOMPLETE'));
        $apiKey = '';
        $options = [];



        if (isset($settings->apiKey) && $settings->apiKey !== '') {
            $apiKey = $settings->apiKey;

            try {
                $vboutApp = new EmailMarketingWS(['api_key' => $settings->apiKey]);
                $lists = $vboutApp->getMyLists();

                if ($lists['count'] > 0) {
                    foreach ($lists['items'] as $item) {
                        $options[$item['id']] = [
                            'name' => $item['name'],
                            'fields' => $item['fields']
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Handle error here... Do nothing for now
            }
        }

        if ($shop->newShop == 1)
        {

        $settingsConfigure = new Setting();
        $listOfSettings = $settingsConfigure->getListActiveSettings($shop->id,'Shopify');
        $settingsHeaders = $settingsConfigure->getListSettingsHeaders('Shopify');
        return view('settingsV2', [
            'listOfSettings' => $listOfSettings,
            'settingsHeaders' => $settingsHeaders,
            'apiKey' => $apiKey,
            'shopifyAppApiKey' => config('app.shopify_app_api_key'),
            'shop' => $shopUrl,
            'settings' => $settings,
            'shopifyFields' => $shopifyFields,
            'shopifyFieldsPurchase' => $shopifyFieldsPurchase,
            'shopifyFieldsIncomplete' => $shopifyFieldsIncomplete,
            'options' => $options,
            'isSetupComplete' => $isSetupComplete,
            'isTrial' => $isTrial,
            'daysLeft' => $daysLeft,
            'upgradeUrl' => $upgradeUrl
        ]);
    }

        return view('settings', [
            'apiKey' => $apiKey,
            'shopifyAppApiKey' => config('app.shopify_app_api_key'),
            'shop' => $shopUrl,
            'settings' => $settings,
            'shopifyFields' => $shopifyFields,
            'shopifyFieldsPurchase' => $shopifyFieldsPurchase,
            'shopifyFieldsIncomplete' => $shopifyFieldsIncomplete,
            'options' => $options,
            'isSetupComplete' => $isSetupComplete,
            'isTrial' => $isTrial,
            'daysLeft' => $daysLeft,
            'upgradeUrl' => $upgradeUrl
        ]);
    }

    public function configurationSettingsUpdate(Request $request, $shopUrl)
    {
         $oldsettings = $request->input('settings');

        $shop = Shop::where('shop_url', $shopUrl)->first();
        $shopsettings = json_decode($shop->settings);
        $shop_apiKey = $shop->apiKey;
        $userName = $request->input('userName');


//        check if Token is available
        if (isset($shop_apiKey) && $shop_apiKey == $oldsettings['apiKey']) {
            $settings = $oldsettings;

            //Will  sync Current Customers

            // Settings Init
            $vboutSettingsSync= new EcommerceWS(['api_key' => $shop_apiKey]);
            $settingsData = $request->configurationList;
            $settingsConfigure = new Setting();
            $settingsFields = $settingsConfigure->getListSettingsHeaders('Shopify');
            foreach ($settingsFields as $settingsFieldKey => $settingsFieldValue)
            {
                 if (isset($settingsData[$settingsFieldKey]))
                     $data[$settingsFieldKey] = 1;
                else $data[$settingsFieldKey] = 0;
            }
            //Sync Current Customers
            if ($data['current_customers'] == 1) {
                $this->syncCurrentCustomersV2($request);
            }
            //Sync Cyrrent Products
            if ($data['sync_current_products'] == 1) {
                $this->syncCurrentProducts($request);
            }
            $settingsRecords = Setting::where('shop_id', $shop->id)->update($data);
            $data['domain'] = $shop->domain;
            $data['apiname'] = 'Shopify';
            if($shop->setup_complete == 0)
                $vboutSettingsSync->sendAPIIntegrationCreation($shop);

            $vboutSettingsSync->sendSettingsSync($data);
            $message = 'Settings updated';
            $shop->setup_complete = 1;
            $shop->save();
            return redirect('settings/' . $shopUrl)->with('success', $message);
        } else {
            $settings = [];
            $settings['apiKey'] = $oldsettings['apiKey'];
            $settings['userName'] = $oldsettings['userName'];
            $shop->settings = json_encode($settings);
            $shop->setup_complete = false;
            $shop->userName = $oldsettings['userName'];
            $shop->apiKey = $oldsettings['apiKey'];
            $shop->save();

            $data['domain'] = $shop->domain;
            $data['apikey'] = $shop->apiKey;
            $data['apiname']= 'Shopify';
            $data['status'] = 1;

            $vboutSettingsSync = new EcommerceWS(['api_key' => $shop_apiKey]);
            $vboutSettingsSync = $vboutSettingsSync->sendAPIIntegrationCreation($shop);

         }
        return redirect('settings/' . $shopUrl);
    }

    public function update(Request $request, $shopUrl)
    {
        $oldsettings = $request->input('settings');
        
        $shop = Shop::where('shop_url', $shopUrl)->first();
		$shopsettings = json_decode($shop->settings);
		
		if(isset($shopsettings->apiKey) && $shopsettings->apiKey == $oldsettings['apiKey']){
			$settings = $oldsettings;
			
			if ($request->input('sync')) {
				$this->syncCurrentCustomers($request);
			}
			
			if (isset($settings['customersList']) && ($settings['customersList']['id'] !== '' || $settings['incompletePurchasesList']['id'] !== '' || $settings['completePurchasesList']['id'] !== '' || $settings['newsLettersList']['id'] !== '') ) {
			   
				if($settings['customersList']['id'] == '') 				unset($settings['customersList']);
				if($settings['incompletePurchasesList']['id'] == '') 	unset($settings['incompletePurchasesList']);
				if($settings['completePurchasesList']['id'] == '') 		unset($settings['completePurchasesList']);
				if($settings['newsLettersList']['id'] == '') 			unset($settings['newsLettersList']);
				
				
			    $shop->settings = json_encode($settings);
				$shop->setup_complete = true;
				$shop->save();
				$message = 'Settings updated';
			   
				return redirect('settings/' . $shopUrl)->with('success', $message);
			}
		}else{
			$settings = [];
			$settings['apiKey'] = $oldsettings['apiKey'];
			$settings['userName'] = $oldsettings['userName'];
			
			$shop->settings = json_encode($settings);
			$shop->setup_complete = false;
			$shop->save();
		}
       
		return redirect('settings/' . $shopUrl);
    }

    public function syncCustomers(Request $request)
    {
        if (isset($request->apiKey) && $request->apiKey !== '') {
            try {
                $customers = (new Customers($request->shopUrl))->all();

                if (count($customers->customers) > 0) {
                    $vboutApp = new EmailMarketingWS(['api_key' => $request->apiKey]);

                    foreach ($customers->customers as $customer) {
                        $fields = [];
                        if ($request->customersListFields) {
                            $listFields = explode(',', $request->customersListFields);
                            
                            // Map the fields
                            foreach ($listFields as $field) {
                                list($fieldKey, $fieldVal) = explode('|', $field);
                                $fieldName = preg_replace('/ /', '_', strtolower($fieldVal));

                                // For customer fields
                                if (isset($customer->$fieldName)) {
                                    $fields[$fieldKey] = $customer->$fieldName;
                                }
                            }
                        }

                        // Request payload
                        $payload = [
                            'email' => $customer->email,
                            'status' => 'Active',
                            'listid' => $request->customersListId,
                            'fields' => $fields
                        ];

                        $vboutApp->addNewContact($payload);
                    }
                }

                return response()->json(['message' => 'Contacts synced']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
        }
    }

    public function isSetupComplete($shopUrl)
    {
        $shop = Shop::where('shop_url', $shopUrl)->first();

        return ($shop) ? $shop->setup_complete : false;
    }

    public function test($apiKey)
    {
        $vboutApp = new EmailMarketingWS(['api_key' => $apiKey]);

        $payload = [
            'id' => 1387596,
            'email' => 'mister1main@example.com',
            'status' => 'Active',
            'listid' => 3914,
            'fields' => ['22220' => 'my first name', '22222' => 'mister1edited@example.com', '22737' => '+1-1234-1234']
        ];

        $result = $vboutApp->updateContact($payload);

        // {"22220":"First Name","22221":"Last Name","22222":"Email Address","22223":"Phone Number","22737":"Phone"}

        return response()->json(['message' => $result]);
    }

    private function syncCurrentCustomersV2(Request $request)
    {
        $shop = Shop::where('shop_url',$request->all()['shop'])->first();
         if (isset($shop->apiKey)){
            try {
                $customers = (new Customers($request->shop))->all();
                $sendData = new EcommerceWS(['api_key' => $shop->apiKey]);
                foreach ($customers->customers as $customerIndex => $customer )
                {
                    $dataCustomer['email']              = $customer->email;
                    $dataCustomer['firstname']          = $customer->first_name;
                    $dataCustomer['lastname']           = $customer->last_name;
                    $dataCustomer['acceptsmarketing']   = $customer->accepts_marketing;
                    $dataCustomer['phone']              = $customer->phone;
                    $dataCustomer['domain'] = $shop->domain;
                    $sendData->Customer($dataCustomer,1);
                }
            } catch (\Exception $e) {
                DB::table('logging')->insert(
                    [
                        'data' => $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine(),
                        'step' => 0,
                        'comment' => 'syncCurrentCustomersV2'
                    ]
                );
            }
        }
    }

    private function syncCurrentProducts(Request $request)
    {
        $shop = Shop::where('shop_url',$request->all()['shop'])->first();
        if (isset($shop->apiKey)){
            try {
                $products = (new Products($request->shop))->all();
                $products = json_decode(json_encode($products, true), true);
                $sendData = new EcommerceWS(['api_key' => $shop->apiKey]);
                $shopifyFields = new ShopfiyFields();
                $shopifyMapFields = new MapFieldsController();
                foreach ($products['products'] as $productIndex => $product )
                {
                    $mappedFields = $shopifyFields->getProductMapField();
                    $dataFields = $shopifyMapFields->ShopifyMapFields($product, $mappedFields);
                    $action = 1;
                    $dataFields['domain'] = $shop->domain;
                    $variants = $product['variants'];
                    $images   = $product['images'];
                    $dataFields['ipaddress'] = '0.0.0.0';
                    $dataFields['discountprice'] = '0.0';
                    $dataFields['link'] = 'https://'.$shop->shop_url.'/products/'.$dataFields['name'];

                    $productData = $dataFields;
                    foreach ($variants as $ItemIndex => $item)
                    {
                        $productData['sku']         = $item['sku'];
                        $productData['productid']   = $item['id'];
                        $productData['price']       = $item['price'];
                        if ( $item['title'] == 'Default Title')
                            $productData['name']  = $dataFields['name'];
                        else $productData['name']  = $dataFields['name'].' ('.$item['title'].')';

                        if (isset($item['image_id']) || ($item['image_id'] != null))
                        {
                            foreach ($images as $imageIndex => $imageValue)
                            {
                                if( $item['image_id'] == $imageValue['id'])
                                {
                                    $productData['image'] = $imageValue['src'];
                                    break;
                                }
                            }
                        }
                        else
                        {
                            if ((isset($product['image']['src']) || ($product['image']['src'] != null)))
                                $productData['image'] = $product['image']['src'];
                        }
                        $sendData->Product($productData,$action);
                    }

                }
            } catch (\Exception $e) {
                DB::table('logging')->insert(
                    [
                        'data' => $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine(),
                        'step' => 0,
                        'comment' => 'syncCurrentProducts'
                    ]
                );
            }
        }
    }

    private function syncCurrentCustomers(Request $request)
    {
        if (isset($request->settings['apiKey']) && $request->settings['apiKey'] !== '') {
            try {
                $customers = (new Customers($request->shop))->all();

                if (count($customers->customers) > 0) {
                    $vboutApp = new EmailMarketingWS(['api_key' => $request->settings['apiKey']]);

                    foreach ($customers->customers as $customer) {
                        $fields = [];
                        if ($request->settings['customersList']['fields']) {
                            // $listFields = explode(',', $request->settings['customersList']['fields']);
                            
                            // Map the fields
                            foreach ($request->settings['customersList']['fields'] as $field) {
                                if ($field) {
                                    list($fieldKey, $fieldVal) = explode('|', $field);
                                    $fieldName = preg_replace('/ /', '_', strtolower($fieldVal));

                                    if ($fieldName === 'phone_number') {
                                        $fieldName = 'phone';
                                    }

                                    if ($fieldName === 'state') {
                                        $fieldName = 'province';
                                    }

                                    // For customer fields
                                    if (isset($customer->$fieldName)) {
                                        if ($fieldName !== 'state') {
                                            $fields[$fieldKey] = $customer->$fieldName;
                                        }
                                    }

                                    // For address related fields
                                    if (isset($customer->default_address) && isset($customer->default_address->$fieldName)) {
                                        if ($customer->default_address->$fieldName !== '') {
                                            $fields[$fieldKey] = $customer->default_address->$fieldName;
                                        }
                                    }
                                }
                            }
                        }

                        // Request payload
                        $payload = [
                            'email' => $customer->email,
                            'status' => 'Active',
                            'listid' => $request->settings['customersList']['id'],
                            'fields' => $fields
                        ];

                        // Check if contact exists in incomplete purchases
                        $contact = $vboutApp->searchContact($customer->email, $request->settings['customersList']['id']);

                        // Add contact to complete purchase
                        if (isset($contact['id']) && $contact['id'] !== '') {
                            $payload['id'] = $contact['id'];
                            $vboutApp->updateContact($payload);
                        } else {
                            $vboutApp->addNewContact($payload);
                        }
                    }
                }
            } catch (\Exception $e) {
                DB::table('logging')->insert(
                    [
                        'data' => $e->getMessage() . ' file: ' . $e->getFile() . ' line: ' . $e->getLine(),
                        'step' => 0,
                        'comment' => 'syncCurrentCustomers'
                    ]
                );
            }
        }
    }

}
