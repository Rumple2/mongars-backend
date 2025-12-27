<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('username', 100)->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password')->nullable();
            $table->text('avatar_url')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('status', ['SINGLE', 'IN_RELATIONSHIP', 'COMPLICATED'])->default('SINGLE');
            $table->enum('auth_method', ['PHONE', 'EMAIL', 'GOOGLE']);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->uuid('couple_id')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index('email');
            $table->index('phone');
            $table->index('username');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
