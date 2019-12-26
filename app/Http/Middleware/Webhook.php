<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AppController;
use App\Libraries\Vbout\Services\EcommerceWS;
use App\Models\Setting;
use App\Models\Shop;
use Closure;
use App\Models\RequestLog;
use DB;
class Webhook
{
    /**
     * f an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Handles duplicate requests based on hmac from the request header of webhooks
        if ($request->header('x-shopify-topic') != 'app/uninstalled') {
            $email = $this->getEmail($request);
            if (!$email) {
//                return response()->json(['message' => 'Email not found']);
                $email = 'no_email_'.$request->header('x-shopify-hmac-sha256');
            }

            $logExists = $this->logExists($request->header('x-shopify-hmac-sha256'), $request->header('x-shopify-topic'), $email);

            if ($logExists) {

                return response()->json(['message' => 'Duplicate request']);
            } else {

                $this->logRequest($request);
                return $next($request);
            }
        } else {
//            $this->uninstall($request->header('x-shopify-shop-domain'));
            return $next($request);
        }
    }

    private function uninstall($request)
    {

        $shop = Shop::where('shop_url', $request)->first();
        $shop_id = $shop->id;
        $shop_apiKey = $shop->apiKey;
        $shop->delete();
        RequestLog::where('shop_url', $request)->delete();
        Setting::where('shop_id',$shop_id)->delete();

        $vboutSettingsSync= new EcommerceWS(['api_key' => $shop_apiKey]);
        $vboutSettingsSync->sendAPIIntegrationCreation($shop,3);
        return response()->json(['success' => 'App uninstalled successfully']);

    }

    private function logRequest($request)
    {
        $email = $this->getEmail($request);

        if ($email) {
            $log = new RequestLog;
            $log->hmac = $request->header('x-shopify-hmac-sha256');
            $log->shop_url = $request->header('x-shopify-shop-domain');
            $log->event = $request->header('x-shopify-topic');
            $log->email = $email;
            $log->payload = $request->getContent();
            $log->save();
        }
    }

    private function logExists($hmac, $event, $email)
    {
        $log = RequestLog::where('hmac', $hmac)
            ->where('event', $event)
            ->where('email', $email)
            ->first();

        if ($log && isset($log->hmac)) {
            return true;
        }

        return false;
    }

    private function getEmail($request)
    {
        if (property_exists($request, 'customer')) {
            return $request->customer['email'];
        } else {
            if ($request->email !== '') {
                return $request->email;
            }
        }

        return false;
    }
}
