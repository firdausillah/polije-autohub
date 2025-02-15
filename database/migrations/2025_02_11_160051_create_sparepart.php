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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('kode')->nullable();
            $table->string('keterangan')->nullable();
            $table->boolean('is_original')->nullable();
            $table->string('part_number')->nullable();
            $table->decimal('komisi_admin', 20, 2)->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
