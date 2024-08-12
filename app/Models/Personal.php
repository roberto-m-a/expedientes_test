<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personal extends Model
{   
    protected $table = 'personal';
    protected $primaryKey = 'IdPersonal';
    use HasFactory;
    protected $fillable = [
        'Nombre',
        'Apellidos',
        'IdDepartamento',
        'Sexo',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class, 'IdPersonal','IdPersonal');
    }
    public function departamento():BelongsTo{
        return $this->belongsTo(Departamento::class,'IdDepartamento', 'IdDepartamento');
    }
}
