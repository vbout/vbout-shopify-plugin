<?php


namespace App\Models;

use App\Libraries\Shopify\ShopfiyFields;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Setting extends  Model
{
    protected $fillable = ['shop_id',
        'abandoned_carts',
        'product_visits',
        'category_visits',
        'customers',
        'product_feed',
        'current_customers',
        'marketing',
        'api_used'
    ];
    public function getListActiveSettings($shop_id,$integration_type)
    {
        $genericListOfSettings = array(
            'search',
            'abandoned_carts',
            'product_visits',
            'category_visits',
            'customers',
            'product_feed',
            'current_customers',
            'sync_current_products',
            'marketing'
        );
        if ($integration_type == 'Shopify')
        {
            $shopifyListOfSettings = array(
//                'search',
                'abandoned_carts',
//                'product_visits',
//                'category_visits',
                'customers',
                'current_customers',
                'product_feed',
                'sync_current_products',
//                'marketing'
            );
            $settingsListFields = $shopifyListOfSettings;
        }
        else $settingsListFields = $genericListOfSettings;

        $settingsList = Setting::where('shop_id',$shop_id)->select($settingsListFields)->first();
        $settingsList = $settingsList->toArray();
        return $settingsList;
    }
    public function getListSettingsHeaders($type)
    {
        $settingsListHeaders = [];

        switch ($type)
        {
            case 'Shopify' :
                $settingsListHeaders = new ShopfiyFields();
                $settingsListHeaders = $settingsListHeaders->getSettingsMapField();
                break;
        }
                return $settingsListHeaders;
    }

}