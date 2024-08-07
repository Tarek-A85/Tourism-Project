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
        Schema::create('package_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained();
            $table->foreignId('date_id')->constrained();
            $table->time('time');
            $table->unsignedInteger('num_of_tickets');
            $table->unsignedInteger('available_tickets');
            $table->integer('current_area')->default(1);
            $table->decimal('delay',5,2)->default(0);
            $table->boolean('auto_tracking')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_details');
        Schema::dropIfExists('package_details');
    }
};
