<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

use App\Libraries\Shopify\Services\ApplicationCharges;
use App\Libraries\Shopify\Services\Shops;
use App\Libraries\Shopify\Services\Webhooks;

class ShopifyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $request = $this->app->make(Request::class);
        $shop = $request->input('shop') ? $request->input('shop') : $request->domain;

        $this->app->singleton(ApplicationCharges::class, function ($app) use ($shop) {
            return new ApplicationCharges($shop);
        });

        $this->app->singleton(Shops::class, function ($app) use ($shop) {
            return new Shops($shop);
        });

        $this->app->singleton(Webhooks::class, function ($app) use ($shop) {    
            return new Webhooks($shop);
        });
    }
}
