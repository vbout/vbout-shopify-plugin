<?php

namespace App\Libraries\Vbout\Services;

// require_once dirname(dirname(__FILE__)) . '/base/Vbout.php';
// require_once dirname(dirname(__FILE__)) . '/base/VboutException.php';
use App\Http\Controllers\AppController;
use App\Libraries\Vbout\Vbout;
use App\Libraries\Vbout\VboutException;
use App\Models\Setting;
use DB;
class ApplicationWS extends Vbout
{
	protected function init()
	{
		$this->api_url = '/app/';
	}
	
    public function getBusinessInfo()
    {	
		$result = array();
		
		try {
			$business = $this->me();

            if ($business != null && isset($business['data'])) {
                $result = array_merge($result, $business['data']['business']);
            }

		} catch (VboutException $ex) {
			$result = $ex->getData();
        }
		
       return $result;
    }

    public function apiCheckStatus()
    {
        $result = array();
        try {
            $domain = $this->checkStatus();

            if ($domain != null && isset($domain['data'])) {
                $result = array_merge($result, $domain['data']['$domain']);
                $this->sendCheckStatus($result);
            }
        }
        catch
            (VboutException $ex) {
                $result = $ex->getData();
            }
        return $result;
    }

    private function sendCheckStatus($domain)
    {
        $result = array();
        try {
            $this->set_method('POST');
            //check status
            $shopActivity = Store::where('domain',$domain)->get();
            $shopId = $shopActivity->pluck('id');
            $shopActivity = $shopActivity->pluck('status');
            $settingsActivity = DB::table('settings')
                ->where('shop_id',$shopId)
                ->select(DB::raw("abandoned_carts + search + product_visits + category_visits + customers + product_feed + current_customers + marketing" ))
                ->get();
            if ( $shopActivity == 0 && $settingsActivity == 0)
                $response['data']  = "You didn't finish setting up and configuring your Integration";
            else if ( $shopActivity == 0)
                $response['data']  = "You didn't finish setting up your Integration";
            else $response['data'] = "You didn't finish configuring your Integration";

            if ($response != null && isset($response['data'])) {
                $result = $response['data'];
            }
        } catch (VboutException $ex) {
            $result = $ex->getData();
        }
        return $result;
    }
}