<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\Vboutify;
use DB;

class WebhookController extends Controller
{
    /**
     * Webhook to sync data from Shopify to Vbout
     * @param  Request $request
     * @return String
     */
    public function sync(Request $request)
    {

        DB::table('logging')->insert(
            [
                'data' => 'None',
                'step' => 0,
                'comment' => 'WEbhook'
            ]
        );
        $vboutify = new Vboutify();
        $result = $vboutify->start($request);
        return response()->json(['message' => $result]);
    }
}