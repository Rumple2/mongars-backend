<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couple_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'CANCELLED'])->default('PENDING');
            $table->text('message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_requests');
    }
};
