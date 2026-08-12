<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_logs', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->enum('direction', ['in', 'out']);
            $table->enum('status', ['success', 'failed']);
            $table->string('owner_name')->nullable();
            $table->timestamp('recognized_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_logs');
    }
};
