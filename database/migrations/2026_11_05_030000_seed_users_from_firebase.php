<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            User::updateOrCreate(
                ['email' => 'handynandaf@gmail.com'],
                [
                    'name' => 'Handy Nanda Fachrizal',
                    'password' => '$2y$12$D8Bi.zNZhVH/tqfSqsirOuu66N6.eE3SLzvO1Ntz7nj1LZGrqbx3y',
                    'role' => 'owner',
                ]
            );

            User::updateOrCreate(
                ['email' => 'handynnda@gmail.com'],
                [
                    'name' => 'Azril Rahandika',
                    'password' => '$2y$12$a5KR0f42v1wclHsPdW0d1uk74C0/vrs2RPyyrTJMQhIQVaxNeaUDO',
                    'role' => 'admin',
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
