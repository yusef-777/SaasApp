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

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name', 30);
            $table->string('ice_no', 15);
            $table->string('if_no')->nullable();
            $table->string('rc_no', 50)->nullable();
            $table->string('csnss_no', 50)->nullable();
            $table->string('address', 60)->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('city', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};