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
        Schema::create('trip_details', function (Blueprint $table) {
            $table->id();
            $table->morphs('detailable');
            $table->foreignId('date_id')->constrained();
            $table->timestamp('time');
            $table->unsignedInteger('num_of_tickets');
            $table->unsignedInteger('available_tickets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_details');
    }
};
