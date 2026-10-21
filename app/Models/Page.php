<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'template',
        'content_blocks',
        'html_content',
        'meta_title',
        'meta_description',
        'is_published',
    ];

    protected $casts = [
        'content_blocks' => 'array',
        'is_published'   => 'boolean',
    ];
}