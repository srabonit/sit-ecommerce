<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormSubmissionRateLimit
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Get Form
        |--------------------------------------------------------------------------
        */

        $form = $request->route('form');

        if (!$form) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Make Sure We Have Form Model
        |--------------------------------------------------------------------------
        */

        if (!is_object($form)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | If IP Limiting Is Disabled
        |--------------------------------------------------------------------------
        |
        | Form-level setting controls everything.
        | If disabled, this middleware does nothing.
        |
        */

        if (
            !isset($form->submission_ip_limit_enabled) ||
            !$form->submission_ip_limit_enabled
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Limit
        |--------------------------------------------------------------------------
        */

        $maxAttempts = (int) (
            $form->submission_limit_count ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Unlimited
        |--------------------------------------------------------------------------
        |
        | 0 / null / empty means unlimited.
        |
        */

        if ($maxAttempts <= 0) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Duration
        |--------------------------------------------------------------------------
        |
        | Duration is stored in minutes.
        |
        | Example:
        | 60  = 1 hour
        | 120 = 2 hours
        | 720 = 12 hours
        | 1440 = 24 hours
        |
        */

        $durationMinutes = (int) (
            $form->submission_limit_duration ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Invalid / Unlimited Duration
        |--------------------------------------------------------------------------
        */

        if ($durationMinutes <= 0) {
            return $next($request);
        }

        $decaySeconds = $durationMinutes * 60;

        /*
        |--------------------------------------------------------------------------
        | Get IP Address
        |--------------------------------------------------------------------------
        */

        $ip = $request->ip();

        /*
        |--------------------------------------------------------------------------
        | Unique Rate Limit Key
        |--------------------------------------------------------------------------
        |
        | Each form has its own separate counter.
        |
        | Example:
        |
        | Form 1 + IP A
        | Form 2 + IP A
        |
        | These are completely separate.
        |
        */

        $key = 'dynamic-form-submit:' .
            $form->id .
            ':' .
            hash('sha256', (string) $ip);

        /*
        |--------------------------------------------------------------------------
        | Laravel Rate Limiter
        |--------------------------------------------------------------------------
        */

        $limiter = app(RateLimiter::class);

        /*
        |--------------------------------------------------------------------------
        | Check Limit
        |--------------------------------------------------------------------------
        */

        if (
            $limiter->tooManyAttempts(
                $key,
                $maxAttempts
            )
        ) {
            $retryAfter = $limiter->availableIn($key);

            /*
            |--------------------------------------------------------------------------
            | Convert Remaining Time
            |--------------------------------------------------------------------------
            */

            $hours = floor($retryAfter / 3600);

            $minutes = floor(
                ($retryAfter % 3600) / 60
            );

            $seconds = $retryAfter % 60;

            if ($hours > 0) {
                $timeMessage =
                    $hours .
                    ' hour' .
                    ($hours > 1 ? 's' : '');

                if ($minutes > 0) {
                    $timeMessage .=
                        ' ' .
                        $minutes .
                        ' minute' .
                        ($minutes > 1 ? 's' : '');
                }
            } elseif ($minutes > 0) {
                $timeMessage =
                    $minutes .
                    ' minute' .
                    ($minutes > 1 ? 's' : '');

                if ($seconds > 0) {
                    $timeMessage .=
                        ' ' .
                        $seconds .
                        ' second' .
                        ($seconds > 1 ? 's' : '');
                }
            } else {
                $timeMessage =
                    max(1, $seconds) .
                    ' second' .
                    ($seconds != 1 ? 's' : '');
            }

            /*
            |--------------------------------------------------------------------------
            | Return JSON Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => false,
                'rate_limited' => true,

                'message' =>
                    'Too many submissions. You have reached the maximum of ' .
                    $maxAttempts .
                    ' submissions allowed for this form. Please try again in ' .
                    $timeMessage .
                    '.',

                'retry_after' => $retryAfter,

                'limit' => $maxAttempts,

                'duration_minutes' => $durationMinutes,
            ], 429)->header(
                'Retry-After',
                (string) $retryAfter
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Count This Request
        |--------------------------------------------------------------------------
        */

        $limiter->hit(
            $key,
            $decaySeconds
        );

        /*
        |--------------------------------------------------------------------------
        | Continue Request
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}