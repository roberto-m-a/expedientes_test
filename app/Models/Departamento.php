<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Departamento extends Model
{
    protected $table = 'departamento';
    protected $primaryKey = 'IdDepartamento';
    use HasFactory;
    protected $fillable = [
        'nombreDepartamento'
    ];
    
    /**
     * Get all of the documento for the Departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documento(): HasMany
    {
        return $this->hasMany(document::class, 'IdDepartamento', 'IdDepartamento');
    }

    /**
     * Get all of the personal for the Departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class, 'IdDepartamento', 'IdDepartamento');
    }
}