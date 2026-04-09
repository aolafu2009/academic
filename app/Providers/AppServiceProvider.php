<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 非生产环境主动捕获惰性加载，提前发现潜在 N+1 查询问题。
        Model::preventLazyLoading(!app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            Log::warning('Detected potential N+1 query via lazy loading.', [
                'model' => get_class($model),
                'relation' => $relation,
            ]);
        });
    }
}
