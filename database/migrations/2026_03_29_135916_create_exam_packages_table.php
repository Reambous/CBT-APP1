<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->integer('time_limit')->default(60);

            // FONDASI BARU: Tipe data ENUM untuk mengunci 4 pilihan kasta paket
            $table->enum('minimum_tier', ['gratis', 'plus', 'pro', 'ultra'])->default('gratis');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_packages');
    }
};
