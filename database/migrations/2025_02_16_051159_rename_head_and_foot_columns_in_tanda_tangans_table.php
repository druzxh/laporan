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
        Schema::table('tanda_tangans', function (Blueprint $table) {
            $table->renameColumn('head', 'jabatan');
            $table->renameColumn('foot', 'nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanda_tangans', function (Blueprint $table) {
            $table->renameColumn('jabatan', 'head');
            $table->renameColumn('nip', 'foot');
        });
    }
};
