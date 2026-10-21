<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * মডেল বুট হওয়ার সময় স্বয়ংক্রিয়ভাবে ইভেন্ট লিসেনার রেজিস্টার করা
     */
    public static function bootAuditable(): void
    {
        // ১. ক্রিয়েট ইভেন্ট অডিট
        static::created(function (Model $model) {
            $module = strtolower(class_basename($model));
            $ref = self::resolveRecordReference($model);

            ActivityLogger::log('create', $module, "Created {$module} #{$model->getKey()}", [
                'record_type' => get_class($model),
                'record_id'   => $model->getKey(),
                'record_ref'  => $ref,
                'branch_id'   => $model->branch_id ?? null,
                'new_values'  => $model->attributesToArray(),
            ]);
        });

        // ২. আপডেট ইভেন্ট অডিট (Old vs New Diff)
        static::updated(function (Model $model) {
            $dirty = $model->getDirty();

            // শুধুমাত্র updated_at পরিবর্তন হলে লগ তৈরি বন্ধ রাখা
            unset($dirty['updated_at']);
            if (empty($dirty)) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $dirty);
            $module = strtolower(class_basename($model));
            $ref = self::resolveRecordReference($model);

            // স্ট্যাটাস পরিবর্তনের জন্য কাস্টম অ্যাকশন নেইম নির্ধারণ
            $action = isset($dirty['status']) || isset($dirty['order_status']) ? 'status_change' : 'update';

            ActivityLogger::log($action, $module, "Updated {$module} #{$model->getKey()}", [
                'record_type' => get_class($model),
                'record_id'   => $model->getKey(),
                'record_ref'  => $ref,
                'branch_id'   => $model->branch_id ?? null,
                'old_values'  => $old,
                'new_values'  => $dirty,
            ]);
        });

        // ৩. ডিলিট ইভেন্ট অডিট
        static::deleted(function (Model $model) {
            $module = strtolower(class_basename($model));
            $ref = self::resolveRecordReference($model);

            ActivityLogger::log('delete', $module, "Deleted {$module} #{$model->getKey()}", [
                'record_type' => get_class($model),
                'record_id'   => $model->getKey(),
                'record_ref'  => $ref,
                'branch_id'   => $model->branch_id ?? null,
                'old_values'  => $model->attributesToArray(),
            ]);
        });

        // ৪. সফট ডিলিট রিস্টোর ইভেন্ট অডিট (যদি SoftDeletes ট্রেইট থাকে)
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $module = strtolower(class_basename($model));
                $ref = self::resolveRecordReference($model);

                ActivityLogger::log('restore', $module, "Restored {$module} #{$model->getKey()}", [
                    'record_type' => get_class($model),
                    'record_id'   => $model->getKey(),
                    'record_ref'  => $ref,
                    'branch_id'   => $model->branch_id ?? null,
                ]);
            });
        }
    }

    /**
     * ডিসপ্লে রেফারেন্স আইডেন্টিফায়ার (Invoice No, Product Code, Title বা Name)
     */
    protected static function resolveRecordReference(Model $model): string
    {
        return $model->invoice_no 
            ?? $model->order_number 
            ?? $model->product_code 
            ?? $model->sku 
            ?? $model->title 
            ?? $model->name 
            ?? '#' . $model->getKey();
    }
}