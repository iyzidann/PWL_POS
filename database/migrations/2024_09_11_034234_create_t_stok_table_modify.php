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
        Schema::table('t_stok', function (Blueprint $table) {
            // Tambahkan kolom supplier_id jika belum ada
            if (!Schema::hasColumn('t_stok', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->index()->nullable(); // Tambahkan kolom supplier_id
                $table->foreign('supplier_id')->references('supplier_id')->on('m_supplier'); // Tambahkan foreign key constraint
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_stok', function (Blueprint $table) {
            if (Schema::hasColumn('t_stok', 'supplier_id')) {
                $table->dropForeign(['supplier_id']); // Hapus foreign key constraint
                $table->dropColumn('supplier_id'); // Hapus kolom supplier_id
            }
        });
    }
};