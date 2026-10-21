<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'admin_email',
        'email_subject',
        'success_message',
        'redirect_url',
        'is_active',
        'save_submissions',
        'send_admin_email',
        'send_user_email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'save_submissions' => 'boolean',
        'send_admin_email' => 'boolean',
        'send_user_email' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class)
            ->orderBy('sort_order');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}