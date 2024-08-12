<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class expediente extends Model
{
    use HasFactory;

    protected $table = "expediente";
    protected $primaryKey = 'IdExpediente';
    protected $fillable = [
        'numDocumentos',
        'IdDocente',
    ];
    /**
     * Get the docente associated with the expediente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'IdDocente', 'IdDocente');
    }
}
