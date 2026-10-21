<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Email Marketing Background Scheduled Tasks
|--------------------------------------------------------------------------
*/

// ১. প্রতি মিনিটে নির্ধারিত সময়ের শিডিউলড ক্যাম্পেইন চেক করে ডিসপ্যাচ করবে
Schedule::command('email-marketing:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

// ২. প্রতি মিনিটে পরিত্যক্ত কার্ট ও অটোমেশনের কাস্টম ডিলে (৫ মিনিট, ১০ মিনিট ইত্যাদি) চেক করে মেইল পাঠাবে
Schedule::command('email-marketing:process-automations')
    ->everyMinute()
    ->withoutOverlapping();