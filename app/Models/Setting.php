<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 't_setting';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'SETTING_KEY',
        'SETTING_VALUE',
        'SETTING_TYPE',
        'DESCRIPTION',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATED_BY',
        'UPDATED_AT'
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime'
    ];

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('SETTING_KEY', $key)->first();
        
        return $setting ? $setting->SETTING_VALUE : $default;
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, string $updatedBy = 'system'): void
    {
        self::updateOrCreate(
            ['SETTING_KEY' => $key],
            [
                'SETTING_VALUE' => $value,
                'UPDATED_BY' => $updatedBy,
                'UPDATED_AT' => now()
            ]
        );
    }

    /**
     * Get multiple settings by keys
     */
    public static function getValues(array $keys): array
    {
        $settings = self::whereIn('SETTING_KEY', $keys)
                       ->pluck('SETTING_VALUE', 'SETTING_KEY')
                       ->toArray();
        
        // Fill missing keys with null
        foreach ($keys as $key) {
            if (!isset($settings[$key])) {
                $settings[$key] = null;
            }
        }
        
        return $settings;
    }
}