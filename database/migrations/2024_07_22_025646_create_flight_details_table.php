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
        Schema::create('flight_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_time_id')->constrained('flight_times')->cascadeOnDelete();
            $table->foreignId('flight_type_id')->constrained('flight_types');
            $table->bigInteger('available_tickets');
            $table->decimal('adult_price', 10, 2);
            $table->decimal('child_price', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_details');
    }
};
