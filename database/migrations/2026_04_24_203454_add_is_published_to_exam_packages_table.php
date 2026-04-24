<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('exam_packages', function (Blueprint $table) {
            // Default false artinya paket baru otomatis jadi Draft (Disembunyikan)
            $table->boolean('is_published')->default(false)->after('minimum_tier');
        });
    }

    public function down()
    {
        Schema::table('exam_packages', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
