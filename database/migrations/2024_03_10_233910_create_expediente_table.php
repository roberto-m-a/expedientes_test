<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expediente', function (Blueprint $table) {
            $table->id('IdExpediente');
            $table->timestamps();
            $table->unsignedInteger('numDocumentos')->default(0);
            $table->foreignId('IdDocente')->references('IdDocente')->on('docente');
        });
        DB::table('expediente')->insert([
            [
                'IdDocente'=>1,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expediente');
    }
};
