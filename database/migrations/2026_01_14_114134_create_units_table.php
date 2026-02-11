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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('unit_number', 50);
            $table->smallInteger('area');
            $table->smallInteger('floor');
            $table->unsignedBigInteger('price');
            $table->string('status', 20)->default('available');
            $table->smallInteger('zone');
            $table->smallInteger('rooms');
            $table->timestamps();

            $table->unique([
                'project_id',
                'unit_number',
                'type',
                'floor',
                'zone',
                'rooms',
            ], 'unique_unit');
        });

     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
