<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| SMS Gateway & Campaign Scheduled Tasks
|--------------------------------------------------------------------------
*/

// ১. প্রতি মিনিটে নির্ধারিত সময়ের শিডিউলড SMS ও মাল্টি-চ্যানেল ক্যাম্পেইন চেক করে কিউতে পাঠাবে
Schedule::command('sms:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// ২. প্রতি ৫ মিনিট পর পর সেন্ট মেসেজগুলোর গেটওয়ে ডেলিভারি স্ট্যাটাস (DLR) ভেরিফাই করবে
Schedule::command('sms:check-delivery')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// ৩. প্রতি ৩০ মিনিট পর পর গেটওয়ের রিয়েল-টাইম ব্যালেন্স সিঙ্ক করবে এবং লো-ব্যালেন্স থ্রেশহোল্ড চেক করবে
Schedule::command('sms:sync-balance')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Email Marketing Background Scheduled Tasks
|--------------------------------------------------------------------------
*/

// ৪. প্রতি মিনিটে নির্ধারিত সময়ের শিডিউলড ক্যাম্পেইন চেক করে ডিসপ্যাচ করবে
Schedule::command('email-marketing:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

// ৫. প্রতি মিনিটে পরিত্যক্ত কার্ট ও অটোমেশনের কাস্টম ডিলে (৫ মিনিট, ১০ মিনিট ইত্যাদি) চেক করে মেইল পাঠাবে
Schedule::command('email-marketing:process-automations')
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Security & Firewall Maintenance Scheduled Tasks
|--------------------------------------------------------------------------
*/

// ৬. প্রতি ঘণ্টায় মেয়াদোত্তীর্ণ IP/নেটওয়ার্ক ব্লক রিলিজ এবং পুরোনো অডিট লগ ক্লিনআপ করবে
Schedule::command('security:maintenance')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| VAT & Tax Period Management Scheduled Tasks
|--------------------------------------------------------------------------
*/

// ৭. প্রতি মাসের ১ তারিখ রাত ১২:০৫ মিনিটে পূর্ববর্তী মাসের ভ্যাট হিসাব ক্লোজ ও অটো-লক করবে
Schedule::command('tax:close-period')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping()
    ->runInBackground();