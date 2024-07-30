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
        Schema::create('package_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained();
            $table->foreignId('trip_detail_id')->constrained('package_details');
            $table->unsignedInteger('children_number');
            $table->unsignedBigInteger('children_total_price');
            $table->unsignedInteger('adult_number');
            $table->unsignedBigInteger('adult_total_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_transactions');
    }
};
