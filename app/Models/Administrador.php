<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Administrador extends Model
{
    protected $table = 'administrador';
    protected $primaryKey ='IdAdministrador';
    use HasFactory;
    protected $fillable = [
        'IdPersonal',
    ];
    public function personal(): HasOne{
        return $this->hasOne(Personal::class, 'IdPersonal', 'IdPersonal');
    }
}
