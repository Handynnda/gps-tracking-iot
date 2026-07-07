<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $table->dropColumn(['pemilik', 'imei']);

            $table->string('kode_kendaraan')->unique()->after('id');

            $table->string('device_id')->unique()->after('plat_nomor');

            $table->enum('status', [
                'Aktif',
                'Nonaktif',
                'Disewa'
            ])->default('Aktif');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $table->string('pemilik');

            $table->string('imei')->nullable();

            $table->dropColumn([
                'kode_kendaraan',
                'device_id',
                'status'
            ]);
        });
    }
};