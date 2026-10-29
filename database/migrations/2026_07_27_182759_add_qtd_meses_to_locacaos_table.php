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
        Schema::table('locacaos', function (Blueprint $table) {
            $table->unsignedInteger('qtd_meses')->nullable()->after('qtd_semanas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locacaos', function (Blueprint $table) {
            $table->dropColumn('qtd_meses');
        });
    }
};
