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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id')->constraint('users')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->timestamps();

            $table->softDeletes('deleted_at', precision: 0);
            $table->unique(['user_id','time_in']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
