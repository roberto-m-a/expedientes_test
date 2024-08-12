<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEscolar extends Model
{
    use HasFactory;
    protected $table = 'periodo_escolar';
    protected $primaryKey = 'IdPeriodoEscolar';
    use HasFactory;
    protected $fillable = [
        'fechaInicio',
        'fechaTermino',
        'nombre_corto',
    ];
    /**
     * Get all of the documento for the PeriodoEscolar
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdPeriodoEscolar', 'IdPeriodoEscolar');
    }
}
