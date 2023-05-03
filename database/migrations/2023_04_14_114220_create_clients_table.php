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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name', 50);
            $table->string('ice', 50);
            $table->string('if_no', 50)->nullable();
            $table->string('rc_no', 50)->nullable();
            $table->string('cnss_no', 50)->nullable();
            $table->string('address', 50)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->unique(['account_id', 'name']);
            $table->unique(['account_id', 'ice']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
