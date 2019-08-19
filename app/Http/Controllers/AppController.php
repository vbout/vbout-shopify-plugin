<?php

namespace App\Http\Controllers;

use App\Libraries\Vbout\Services\ApplicationWS;
use App\Libraries\Vbout\Services\EcommerceWS;
use App\Models\Setting;
use Illuminate\Http\Request;
use phpish\shopify;
use App\Models\Shop;
use App\Models\RequestLog;
use App\Libraries\Vboutify;
use App\Libraries\Shopify\Services\ApplicationCharges;
use App\Libraries\Shopify\Services\Shops;
use App\Libraries\Shopify\Services\Webhooks;
use App\Libraries\Vbout\Services\EmailMarketingWS;
use DB;
/**
 * Shopify app controller
 */
class AppController extends Controller
{
    private $apiKey;
    private $sharedSecret;
    private $redirectUrl;
    private $scope;
    private $successUrl;
    private $webhookUrl;

    private $appCharges;
    private $shops;
    private $webhooks;

    /**
     * Instantiate configurations
     */
    public function __construct(ApplicationCharges $appCharges, Shops $shops, Webhooks $webhooks)
    {
        $this->apiKey = env('SHOPIFY_APP_API_KEY');
        $this->sharedSecret = env('SHOPIFY_APP_SHARED_SECRET');
        $this->redirectUrl = env('SHOPIFY_APP_REDIRECT_URL');
        $this->scope = explode(',', env('SHOPIFY_APP_SCOPE'));
        $this->successUrl = env('SHOPIFY_APP_SUCCESS_URL');
        $this->webhookUrl = env('SHOPIFY_APP_WEBHOOK_URL');

        // Instantiate services
        $this->appCharges = $appCharges;
        $this->shops = $shops;
        $this->webhooks = $webhooks;
    }

    /**
     * Install Shopify app
     * @param  Request $request
     * @return Redirect
     */
    public function install(Request $request)
    {
        $shopUrl = $request->input('shop');

        // Shop URL is required
        if (!$shopUrl) {
            // return response()->json(['error' => '[shop] parameter not set'], 404);
            return view('message', ['message' => '[shop] parameter not set']);
        }

        // Shop URL must be a valid Shopify URL
        if (!preg_match('/^[a-zA-Z0-9\-]+.myshopify.com$/', $shopUrl)) {
            // return response()->json(['error' => $shopUrl . ' is not a valid Shopify URL'], 422);
            return view('message', ['message' => $shopUrl . ' is not a valid Shopify URL']);
        }

        // Check if shop is already installed
        if ($this->shopExists($shopUrl)) {
            // return response()->json(['error' => $shopUrl . ' is already installed'], 422);
            return view('message', ['message' => $shopUrl . ' is already installed']);
        }

        $installUrl = shopify\install_url($shopUrl, $this->apiKey);
        return redirect($installUrl);
    }

    /**Sh
     * Start Shopify App
     * @param  Request $request
     * @return Redirect
     */
    public function start(Request $request)
    {

        $shopUrl = $request->input('shop');
        $code = $request->input('code');
		$upgradeplan = $request->input('upgradeplan');
        // Shop URL is required
        if (!$shopUrl) {
            return response()->json(['error' => '[shop] parameter not set'], 404);
        }

        // If shop is already installed, display app settings
        if ($this->shopExists($shopUrl)) {
            $shop = Shop::where('shop_url', $shopUrl)->first();

            $setting = Setting::where('shop_id',$shop->id)->get();
            if(sizeof($setting) == 0  )
            {
                DB::table('settings')
                    ->insert([
                        'created_at' =>date("Y-m-d H:i:s"),
                        'updated_at'  => date("Y-m-d H:i:s"),
                        'shop_id'   => $shop->id,
                        'api_used'  =>'Shopify',
                    ]);
            }
            //$isTrial = $shop->trial;
			//$remainingDays = $shop->remaining_days;


			/*if($upgradeplan && $shop->trial){
				$response = $this->appCharges->create('Basic Plan - Test', 0.5, secure_url('charge?shop=' . $shopUrl), true);

				if (isset($response->application_charge)) {
					// Must return a script to redirect outside of Shopify's iframe
					return '<script> window.top.location.href = "' . $response->application_charge->confirmation_url . '";</script>';
				}
			}
			
			// Redirect to application charge for payment if 14-day free trial expires
            //if (config('app.shopify_app_charge') && $remainingDays > config('app.shopify_app_trial_days') && $isTrial) {
			//if(config('app.shopify_app_charge') && $shop->trial){ // if trial			
			
			else if($shop->trial){ 		
				$created_at = $shop->created_at;			
				$today_date = time();
				$created_date = strtotime($created_at);
				$datediff = $today_date - $created_date;
				$dayspassed = floor($datediff / (60 * 60 * 24));
				
				// if trial, check days if trial period expired	
				if ( $dayspassed >= config('app.shopify_app_trial_days')) {
					
					$response = $this->appCharges->create('Basic Plan - Test', 0.5, secure_url('charge?shop=' . $shopUrl), true);

					if (isset($response->application_charge)) {
						// Must return a script to redirect outside of Shopify's iframe
						return '<script> window.top.location.href = "' . $response->application_charge->confirmation_url . '";</script>';
					}				
				}			
			}*/
			
			return redirect('settings/' . $shopUrl);
            
        }
        if (!$code) {
            $authorizeUrl = shopify\authorization_url($shopUrl, $this->apiKey, $this->scope, $this->redirectUrl);
            return redirect($authorizeUrl);
        }
    }

    /**
     * After authorization it will redirect to this callback method to get and store access token
     * @param  Request $request
     * @return Redirect
     */
    public function callback(Request $request)
    {
        $shopUrl = $request->input('shop');
        $code = $request->input('code');

        if (!$shopUrl) {
            return response()->json(['error' => '[shop] parameter not set'], 404);
        }

        if (!$code) {
            return response()->json(['error' => '[code] parameter not set'], 404);
        }

        // If shop is already installed, display a message
        if ($this->shopExists($shopUrl)) {
            return view('message', [
                'message' => 'Shop (' . $shopUrl . ') is already installed',
                'shop' => $shopUrl
            ]);
        }

        try {
            $oAuthToken = shopify\access_token($shopUrl, $this->apiKey, $this->sharedSecret, $code);

            if ($oAuthToken) {
                // Store token
                $this->storeShop($shopUrl, $oAuthToken);

                // Create webhooks
                $this->createWebhooks($shopUrl);

                // Redirect to Vbout's registration with auto-populated fields
                return redirect('https://' . $shopUrl . '/admin/apps/');
            }
        } catch (shopify\ApiException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (shopify\CurlException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Uninstall app and remove necessary components
     * @param  Request $request
     * @return String
     */
    public function UNINSTALL(Request $request)
    {

        // No need to delete webhooks that are created using webhook API. See https://ecommerce.shopify.com/c/shopify-apis-and-technology/t/app-uninstalled-error-401-unauthorized-token-when-trying-to-deregister-webhooks-399712
        // $this->deleteWebhooks($request->domain);
        $shop = Shop::where('shop_url', $request->domain)->get();
        $shop_id = $shop->id;

        $shop->delete();
        RequestLog::where('shop_url', $request->domain)->delete();
        Setting::where('shop_id',$shop_id)->delete();
        $vboutSettingsSync= new EcommerceWS(['api_key' => $shop->apiKey]);
        $vboutSettingsSync->sendAPIIntegrationCreation($shop,3);

        return response()->json(['success' => 'App uninstalled successfully']);
    }

    public function success($shopUrl)
    {
        return view('success', ['shop' => $shopUrl]);
    }

    public function charge(Request $request)
    {
        $chargeId = $request->input('charge_id');
        $shopUrl = $request->input('shop');
 
        if ($chargeId && $chargeId !== '') {
            $status = $this->appCharges->activate($chargeId);

            if ($status === 'declined') {
                return redirect('https://' . $shopUrl . '/admin/apps');
            }

            if ($status === 'active') {
                $shop = Shop::where('shop_url', $shopUrl)->first();
                $shop->trial = false;
                $shop->save();

                return redirect('settings/' . $shopUrl);
            }
        }
    }

    /**
     * Check if the shop is already installed
     * @param  String $shopUrl
     * @return Array
     */
    private function shopExists($shopUrl)
    {
        return Shop::where('shop_url', $shopUrl)->get()->toArray();
    }

    /**
     * Store shop and token in the database upon installation
     * @param  String $shopUrl
     * @param  String $token
     * @return Resource
     */
    private function storeShop($shopUrl, $token)
    {
        $shop = new Shop;
        $shop->shop_url = $shopUrl;
        $shop->token = $token;
        $shop->save();
    }

    /**
     * Create webhooks
     * @param  String $shopUrl
     * @return Resource
     */
    private function createWebhooks($shopUrl)
    {
        $webhookList = explode(',', env('SHOPIFY_APP_WEBHOOKS'));

        if (count($webhookList) > 0) {
            foreach ($webhookList as $webhook) {

                $this->webhooks->create($webhook, $this->webhookUrl . '/sync');
            }
            $this->webhooks->create('app/uninstalled', $this->webhookUrl . '/uninstall');
        }
    }

    /**
     * Delete webhooks
     * @param  String $shopUrl
     * @return Resource
     */
    private function deleteWebhooks($shopUrl)
    {
        $list = $this->webhooks->all();
        print_r($list);

        if ($list && isset($list->webhooks)) {
            foreach ($list->webhooks as $hooks) {
                $this->webhooks->delete($hooks->id);
            }
        }
    }


    public function checkActive($domain)
    {
        $shopActivity = Store::where('domain',$domain)->get();
        $shopId = $shopActivity->pluck('id');
        $shopActivity = $shopActivity->pluck('status');
        $settingsActivity = DB::table('settings')
            ->where('shop_id',$shopId)
            ->select(DB::raw("abandoned_carts + search + product_visits + category_visits + customers + product_feed + current_customers + marketing" ))
            ->get();
        if ( $shopActivity == 0 && $settingsActivity == 0)
            $response = "You didn't finish setting up and configuring your Integration";
        else if ( $shopActivity == 0)
            $response = "You didn't finish setting up your Integration";
        else $response = "You didn't finish configuring your Integration";
        return $response;
    }

    /**
     * Generate field parameters
     * @param  String $shopUrl
     * @return String
     */
    private function generateFieldParams($shopUrl)
    {
        $shops = new Shops($shopUrl);
        $shopInfo = $shops->info();

        if (isset($shopInfo->shop)) {
            $params = [
                'company' => $shopInfo->shop->name,
                'contact' => $shopInfo->shop->shop_owner,
                'email' => $shopInfo->shop->email,
                'phone' => $shopInfo->shop->phone
            ];

            return base64_encode(json_encode($params));
        }

        return false;
    }
}
