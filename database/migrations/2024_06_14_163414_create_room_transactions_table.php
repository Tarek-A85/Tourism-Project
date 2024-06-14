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
        Schema::create('room_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->integer('number_of_rooms');
            $table->foreignId('staying_date_id')->constrained('dates');
            $table->foreignId('departure_date_id')->constrained('dates');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_transactions');
    }
};
