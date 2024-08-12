<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            //$table->string('name');
            //$table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreignId('IdPersonal')->references('IdPersonal')->on('personal');
        });
        DB::table('users')->insert([
            [
                'id'=>10000,
                'email'=>'admin.admin@oaxaca.tecnm.mx',
                'email_verified_at'=>now(),
                'password'=>Hash::make('y,dX9^/2e0tc'),
                'IdPersonal'=>10000,
            ],
            [
                'id'=>10001,
                'email'=>'secre.secre@oaxaca.tecnm.mx',
                'email_verified_at'=>now(),
                'password'=>Hash::make('l~NZ7O2.9Vk4'),
                'IdPersonal'=>10001,
            ],
            [
                'id'=>10002,
                'email'=>'docente.test@oaxaca.tecnm.mx',
                'email_verified_at'=>now(),
                'password'=>Hash::make('docentePass7@'),
                'IdPersonal'=>10002,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
