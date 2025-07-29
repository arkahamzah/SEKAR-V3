<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekarRole extends Model
{
    protected $table = 't_sekar_roles';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'NAME',
        'DESC',
        'IS_AKTIF'
    ];

    public function pengurus()
    {
        return $this->hasMany(SekarPengurus::class, 'ID_ROLES', 'ID');
    }
}
