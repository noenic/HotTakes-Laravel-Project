<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::create('vehicules', function(Blueprint $table){
            $table->String('matricule')->primary();
            $table->String('model');
            $table->Integer('nombredeplace');
            $table->Double('prix');
            $table->Boolean('disponible');

        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
