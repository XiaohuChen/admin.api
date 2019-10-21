<?php

namespace App\Providers;

use App\Models\AdminUserModel;
use App\Models\BaseModel;
use Illuminate\Support\ServiceProvider;
use Observer\AdminLogObserver;
use Observer\AdminUserObserver;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
//        AdminUserModel::observe(AdminUserObserver::class);//注册AdminUser模型观察者
//        BaseModel::observe(AdminLogObserver::class);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
