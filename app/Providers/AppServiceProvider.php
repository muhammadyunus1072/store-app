<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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

        Blade::directive('dateTimeFull', function ($expression) {
            return "<?php echo $expression ? Carbon\Carbon::parse($expression)->translatedFormat('d F Y, H:i') : $expression; ?>";
        });

        Blade::directive('currency', function ($expression) {
            return "<?php echo App\Helpers\NumberFormatter::format($expression); ?>";
        });
        $this->loadMigrationsFrom([
            database_path('migrations'), // Default
            database_path('migrations/core'),
            database_path('migrations/logistic/*'),
            database_path('migrations/document/*'),
            database_path('migrations/purchasing/*'),
            database_path('migrations/sales/*'),
            database_path('migrations/finance/*'),
        ]);
    }
}
