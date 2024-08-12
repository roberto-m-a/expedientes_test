<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    protected $table = 'tipo_documento';
    protected $primaryKey = 'IdTipoDocumento';
    use HasFactory;
    protected $fillable = [
        'nombreTipoDoc',
    ];
    /**
     * Obtener la coleccion de documentos con relacion al TipoDocumento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdTipoDocumento', 'IdTipoDocumento');
    }
}
