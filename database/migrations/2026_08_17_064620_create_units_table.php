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
            $table->ulid('ulid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('unit_number');
            $table->unsignedSmallInteger('floor')->nullable();
            $table->decimal('size_sqm', 8, 2)->nullable();
            $table->unsignedTinyInteger('rooms')->nullable();
            $table->string('status')->default('vacant');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'unit_number']);
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
