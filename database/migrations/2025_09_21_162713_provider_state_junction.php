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
        Schema::create('provider_state_junction', function (Blueprint $table) {

            $table->foreignId('provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->foreignId('provider_state_id')->constrained('states')->onDelete('cascade');

            //primary key
            $table->primary(['provider_id', 'provider_state_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_state_junction');
    }
};
