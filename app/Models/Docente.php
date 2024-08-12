<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Docente extends Model
{   
    protected $table = 'docente';
    protected $primaryKey = 'IdDocente';
    use HasFactory;
    protected $fillable = [
        'GradoAcademico',
        'IdPersonal',
    ];
    public function personal(): HasOne{
        return $this->hasOne(Personal::class,'IdPersonal','IdPersonal');
    }
    /**
     * Get the expediente associated with the Docente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expediente(): HasOne
    {
        return $this->hasOne(expediente::class, 'IdDocente', 'IdDocente');
    }
}
