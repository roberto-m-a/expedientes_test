<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('administrador', function (Blueprint $table) {
            $table->id('IdAdministrador');
            $table->timestamps();
            $table->foreignId('IdPersonal')->references('IdPersonal')->on('personal');
        });
        DB::table('administrador')->insert([
            [
                'IdAdministrador'=>1,
                'IdPersonal'=>10000,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrador');
    }
};
