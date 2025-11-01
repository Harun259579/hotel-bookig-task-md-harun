<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->foreignId('category_id')->constrained('room_categories');
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedInteger('nights');
            $table->decimal('base_total', 10, 2);
            $table->decimal('weekend_surcharge', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('final_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
