<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'show_title',
        'slug',
        'description',
        'show_description',
        'logo',
        'styles',
        'theme_preset',
        'submit_button_text',
        'submit_button_class',
        'custom_css',
        'admin_email',
        'email_subject',
        'send_admin_email',
        'user_email_subject',
        'send_user_email',
        'success_message',
        'redirect_url',
        'is_active',
        'save_submissions',
        'captcha_type',
        'rate_limit_per_ip',
        'prevent_duplicates',
        'webhook_url',
        'webhook_method',
        'webhook_auth_header',
    ];

    protected $casts = [
        'show_title'         => 'boolean',
        'show_description'   => 'boolean',
        'styles'             => 'array',
        'is_active'          => 'boolean',
        'save_submissions'   => 'boolean',
        'send_admin_email'   => 'boolean',
        'send_user_email'    => 'boolean',
        'prevent_duplicates' => 'boolean',
        'rate_limit_per_ip'  => 'integer',
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