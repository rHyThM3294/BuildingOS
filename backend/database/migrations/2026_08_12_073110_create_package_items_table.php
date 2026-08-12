<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_no');
            $table->string('recipient_unit');
            $table->string('recipient_name');
            $table->string('courier')->nullable();
            $table->enum('status', ['pending', 'notified', 'collected'])->default('pending');
            $table->timestamp('arrived_at');
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
    }
};
