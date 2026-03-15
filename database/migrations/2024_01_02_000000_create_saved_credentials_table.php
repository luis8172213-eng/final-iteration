<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a table for storing AES-encrypted login credentials.
     * All sensitive fields (site_name, site_url, username, password, notes) 
     * are stored as TEXT to accommodate encrypted data.
     */
    public function up(): void
    {
        Schema::create('saved_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('site_name');           // AES encrypted
            $table->text('site_url')->nullable(); // AES encrypted
            $table->text('username');             // AES encrypted
            $table->text('password');             // AES encrypted
            $table->text('notes')->nullable();    // AES encrypted
            $table->timestamps();
            
            // Index for faster user lookups
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_credentials');
    }
};
