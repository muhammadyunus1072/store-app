<?php

namespace App\Providers;

use App\Helpers\Core\UserStateHandler;
use App\Settings\SettingCore;
use App\Settings\SettingLogistic;
use App\Settings\SettingPurchasing;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Contracts\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingCore::class, function (Application $app) {
            return new SettingCore();
        });

        $this->app->singleton(SettingLogistic::class, function (Application $app) {
            return new SettingLogistic();
        });

        $this->app->singleton(SettingPurchasing::class, function (Application $app) {
            return new SettingPurchasing();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            database_path('migrations'), // Default
            database_path('migrations/core'),
            database_path('migrations/logistic/*'),
            database_path('migrations/document/*'),
            database_path('migrations/purchasing/*'),
            database_path('migrations/sales/*'),
            database_path('migrations/finance/*'),
        ]);

        Blade::directive('dateTime', function ($expression) {
            return "<?php echo $expression ? Carbon\Carbon::parse($expression)->translatedFormat('d F Y, H:i') : $expression; ?>";
        });

        Blade::directive('date', function ($expression) {
            return "<?php echo $expression ? Carbon\Carbon::parse($expression)->translatedFormat('d F Y') : $expression; ?>";
        });

        Blade::directive('currency', function ($expression) {
            return "<?php echo App\Helpers\General\NumberFormatter::format($expression); ?>";
        });

        $this->app->singleton(UserStateHandler::class, function (Application $app) {
            return new UserStateHandler();
        });
    }
}
