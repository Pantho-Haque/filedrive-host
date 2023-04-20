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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_pic')->nullable();
            $table->unsignedBigInteger('number_of_folders')->default(0);
            $table->unsignedBigInteger('number_of_files')->default(0);
            $table->boolean('isAdmin')->default(false);
            $table->boolean('visibility')->default(true);
            $table->unsignedBigInteger('used_storage')->default(0);
            $table->unsignedBigInteger('total_storage')->default(52428800);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
