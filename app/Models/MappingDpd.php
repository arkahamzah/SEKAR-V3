<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MappingDpd extends Model
{
    protected $table = 'mapping_dpd';
    
    protected $fillable = [
        'prefix6',
        'dpd', 
        'dpw'
    ];

    public static function getDpwByDpd(string $dpd): string
    {
        $mapping = self::where('dpd', $dpd)->first();
        return $mapping ? $mapping->dpw : 'DPW JABAR 2';
    }
}