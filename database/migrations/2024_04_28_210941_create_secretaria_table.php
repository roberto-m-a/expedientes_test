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
        Schema::create('secretaria', function (Blueprint $table) {
            $table->id('IdSecretaria');
            $table->timestamps();
            $table->foreignId('IdPersonal')->references('IdPersonal')->on('personal');
        });
        DB::table('secretaria')->insert([
            [
                'IdSecretaria'=>1,
                'IdPersonal'=>10001,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretaria');
    }
};
