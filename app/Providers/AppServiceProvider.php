<?php

namespace App\Providers;

use App\Model\Banner;
use App\Model\BusinessSetting;
use App\Model\Category;
use App\Models\LoginSetup;
use App\Observers\BannerObserver;
use App\Observers\BusinessSettingObserver;
use App\Observers\CategoryObserver;
use App\Observers\LoginSetupObserver;
use App\Traits\SystemAddonTrait;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    use SystemAddonTrait;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        BusinessSetting::observe(BusinessSettingObserver::class);
        LoginSetup::observe(LoginSetupObserver::class);
        Banner::observe(BannerObserver::class);
        Category::observe(CategoryObserver::class);

        //for system addon
        Config::set('addon_admin_routes',$this->get_addon_admin_routes());
        Config::set('get_payment_publish_status',$this->get_payment_publish_status());

        try {
            $timezone = BusinessSetting::where(['key' => 'time_zone'])->first();
            if (isset($timezone)) {
                config(['app.timezone' => $timezone->value]);
                date_default_timezone_set($timezone->value);
            }
        }catch(\Exception $exception){}
        Paginator::useBootstrap();

    }
}
