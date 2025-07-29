<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jajaran extends Model
{
    protected $table = 'm_jajaran';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'NAMA_JAJARAN',
        'IS_AKTIF'
    ];

    public function sekarJajaran()
    {
        return $this->hasMany(SekarJajaran::class, 'ID_JAJARAN', 'ID');
    }
}