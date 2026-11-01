<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Observers\OrderSmsObserver;
use App\Listeners\SendCustomerWelcomeSmsNotification;

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
        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | Custom Rate Limiter (200 req/min)
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('custom-rate-limit', function (Request $request) {
            return Limit::perMinute(200)
                ->by($request->ip())
                ->response(function () {
                    return response()->view('errors.429', [], 429);
                });
        });

        /*
        |--------------------------------------------------------------------------
        | Authentication Rate Limiter
        |--------------------------------------------------------------------------
        */
        RateLimiter::for('auth-limiter', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | Dynamic System Settings (Timezone, Site Title & Date Format)
        |--------------------------------------------------------------------------
        */
        try {
            if (Schema::hasTable('settings')) {
                $systemSettings = DB::table('settings')
                    ->whereIn('key', ['site_title', 'timezone', 'date_format'])
                    ->pluck('value', 'key');

                // 1. Dynamic Site Title
                if (!empty($systemSettings['site_title'])) {
                    Config::set('app.name', $systemSettings['site_title']);
                }

                // 2. Dynamic Global Timezone Injection (PHP & Laravel runtime)
                $selectedTimezone = !empty($systemSettings['timezone']) ? trim($systemSettings['timezone']) : 'Asia/Dhaka';
                if (!empty($selectedTimezone) && in_array($selectedTimezone, \DateTimeZone::listIdentifiers())) {
                    date_default_timezone_set($selectedTimezone);
                    Config::set('app.timezone', $selectedTimezone);
                }

                // 3. Dynamic Date Format
                if (!empty($systemSettings['date_format'])) {
                    Config::set('app.date_format', $systemSettings['date_format']);
                }
            }
        } catch (\Throwable $e) {
            // Suppress errors during early CLI migrations
        }

        /*
        |--------------------------------------------------------------------------
        | Universal Media Upload Directories Auto-Initialization
        |--------------------------------------------------------------------------
        */
        try {
            $publicUploads = public_path('uploads');
            if (!file_exists($publicUploads)) {
                @mkdir($publicUploads, 0777, true);
            }
            $storagePublic = storage_path('app/public');
            if (!file_exists($storagePublic)) {
                @mkdir($storagePublic, 0777, true);
            }
        } catch (\Throwable $th) {
            // Suppress bootstrap mkdir warnings safely
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic Form Blade Directive
        |--------------------------------------------------------------------------
        */
        Blade::directive('form', function ($expression) {
            return "<?php echo app(\\App\\Services\\FormRenderer::class)->render($expression); ?>";
        });

        /*
        |--------------------------------------------------------------------------
        | Model Event Observers (Abandoned Cart Sync & Automated SMS Alerts)
        |--------------------------------------------------------------------------
        */
        try {
            if (class_exists(Order::class)) {
                // Existing Order Observer (Abandoned Cart Auto-Recovery Sync)
                if (class_exists(OrderObserver::class)) {
                    Order::observe(OrderObserver::class);
                }

                // SMS Automation Observer (Order Placed & Status Updates)
                if (class_exists(OrderSmsObserver::class)) {
                    Order::observe(OrderSmsObserver::class);
                }
            }
        } catch (\Throwable $e) {
            // Suppress errors during early migrations
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Auth Events & SMS Listeners
        |--------------------------------------------------------------------------
        */
        try {
            if (class_exists(Registered::class) && class_exists(SendCustomerWelcomeSmsNotification::class)) {
                Event::listen(
                    Registered::class,
                    SendCustomerWelcomeSmsNotification::class
                );
            }
        } catch (\Throwable $e) {
            // Suppress errors during early migrations
        }
    }
}