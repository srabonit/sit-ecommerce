<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SmsTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'template_key',
        'event_type',
        'sender_id',
        'sender_type',
        'body',
        'message',
        'variables',
        'available_variables',
        'description',
        'is_system',
        'is_active',
        'is_unicode',
        'estimated_parts',
    ];

    protected $casts = [
        'variables'           => 'array',
        'available_variables' => 'array',
        'is_active'           => 'boolean',
        'is_unicode'          => 'boolean',
        'is_system'           => 'boolean',
        'estimated_parts'     => 'integer',
    ];

    /**
     * Model booted lifecycle hooks to guarantee unicode check and schema safety.
     */
    protected static function booted(): void
    {
        static::saving(function (SmsTemplate $template) {
            $content = $template->body ?? $template->message ?? '';

            // Check and set unicode boolean
            $template->is_unicode = static::containsUnicode((string) $content);

            // Calculate estimated SMS parts
            if (empty($template->estimated_parts)) {
                $len = mb_strlen((string) $content, 'UTF-8');
                if ($template->is_unicode) {
                    $template->estimated_parts = $len <= 70 ? 1 : (int) ceil($len / 67);
                } else {
                    $template->estimated_parts = $len <= 160 ? 1 : (int) ceil($len / 153);
                }
            }

            // Sync column aliases based on actual DB table schema
            static::syncColumnAliases($template);
        });
    }

    /**
     * Safely balance aliases (key/template_key, body/message, variables/available_variables)
     * without triggering SQL Column Not Found exceptions.
     */
    protected static function syncColumnAliases(SmsTemplate $template): void
    {
        if (!Schema::hasTable('sms_templates')) {
            return;
        }

        // 1. Key alias sync
        $hasKey = Schema::hasColumn('sms_templates', 'key');
        $hasTplKey = Schema::hasColumn('sms_templates', 'template_key');
        $keyValue = $template->attributes['key'] ?? $template->attributes['template_key'] ?? null;

        if ($hasKey && $hasTplKey) {
            $template->attributes['key'] = $keyValue;
            $template->attributes['template_key'] = $keyValue;
        } elseif ($hasKey) {
            $template->attributes['key'] = $keyValue;
            unset($template->attributes['template_key']);
        } elseif ($hasTplKey) {
            $template->attributes['template_key'] = $keyValue;
            unset($template->attributes['key']);
        }

        // 2. Message / Body alias sync
        $hasBody = Schema::hasColumn('sms_templates', 'body');
        $hasMsg = Schema::hasColumn('sms_templates', 'message');
        $bodyValue = $template->attributes['body'] ?? $template->attributes['message'] ?? null;

        if ($hasBody && $hasMsg) {
            $template->attributes['body'] = $bodyValue;
            $template->attributes['message'] = $bodyValue;
        } elseif ($hasBody) {
            $template->attributes['body'] = $bodyValue;
            unset($template->attributes['message']);
        } elseif ($hasMsg) {
            $template->attributes['message'] = $bodyValue;
            unset($template->attributes['body']);
        }

        // 3. Variables alias sync
        $hasVars = Schema::hasColumn('sms_templates', 'variables');
        $hasAvailVars = Schema::hasColumn('sms_templates', 'available_variables');
        $varsValue = $template->attributes['variables'] ?? $template->attributes['available_variables'] ?? null;

        if ($hasVars && $hasAvailVars) {
            $template->attributes['variables'] = $varsValue;
            $template->attributes['available_variables'] = $varsValue;
        } elseif ($hasVars) {
            $template->attributes['variables'] = $varsValue;
            unset($template->attributes['available_variables']);
        } elseif ($hasAvailVars) {
            $template->attributes['available_variables'] = $varsValue;
            unset($template->attributes['variables']);
        }

        // 4. Strip attributes if corresponding columns do not exist in table
        if (!Schema::hasColumn('sms_templates', 'is_system')) {
            unset($template->attributes['is_system']);
        }
        if (!Schema::hasColumn('sms_templates', 'estimated_parts')) {
            unset($template->attributes['estimated_parts']);
        }
        if (!Schema::hasColumn('sms_templates', 'description')) {
            unset($template->attributes['description']);
        }
    }

    /**
     * Check if a text contains non-GSM characters (Bangla or emojis)
     */
    public static function containsUnicode(string $text): bool
    {
        return (bool) preg_match('/[^\x20-\x7E\r\n\t]/u', $text);
    }

    /**
     * Accessor for key
     */
    public function getKeyAttribute(): ?string
    {
        return $this->attributes['key'] ?? $this->attributes['template_key'] ?? null;
    }

    /**
     * Accessor for template_key
     */
    public function getTemplateKeyAttribute(): ?string
    {
        return $this->attributes['template_key'] ?? $this->attributes['key'] ?? null;
    }

    /**
     * Accessor for body
     */
    public function getBodyAttribute(): ?string
    {
        return $this->attributes['body'] ?? $this->attributes['message'] ?? null;
    }

    /**
     * Accessor for message
     */
    public function getMessageAttribute(): ?string
    {
        return $this->attributes['message'] ?? $this->attributes['body'] ?? null;
    }

    /**
     * Accessor for variables
     */
    public function getVariablesAttribute(): mixed
    {
        $val = $this->attributes['variables'] ?? $this->attributes['available_variables'] ?? null;
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return $decoded !== null ? $decoded : array_filter(array_map('trim', explode(',', $val)));
        }
        return $val ?? [];
    }

    /**
     * Accessor for available_variables
     */
    public function getAvailableVariablesAttribute(): mixed
    {
        return $this->getVariablesAttribute();
    }

    /**
     * Substitute dynamic tags with actual data values
     * Example: {customer_name} or {{ customer_name }} -> "John Doe"
     */
    public function render(array $data = []): string
    {
        $rendered = (string) ($this->body ?? $this->message ?? '');

        foreach ($data as $key => $value) {
            $rendered = str_replace(
                ['{' . $key . '}', '{{' . $key . '}}', '{{ ' . $key . ' }}'],
                (string) $value,
                $rendered
            );
        }

        return $rendered;
    }

    /**
     * Find an active template safely by its unique key
     */
    public static function getActive(string $templateKey): ?self
    {
        return static::where(function ($q) use ($templateKey) {
                $hasQueried = false;
                if (Schema::hasColumn('sms_templates', 'key')) {
                    $q->where('key', $templateKey);
                    $hasQueried = true;
                }
                if (Schema::hasColumn('sms_templates', 'template_key')) {
                    if ($hasQueried) {
                        $q->orWhere('template_key', $templateKey);
                    } else {
                        $q->where('template_key', $templateKey);
                    }
                }
            })
            ->where('is_active', true)
            ->first();
    }
}