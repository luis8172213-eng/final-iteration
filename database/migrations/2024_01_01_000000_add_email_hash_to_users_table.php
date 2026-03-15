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
        Schema::table('users', function (Blueprint $table) {
            // Change email column to TEXT to store encrypted data
            $table->text('email')->change();
            
            // Add email_hash column for lookups (since email is now encrypted)
            $table->string('email_hash')->after('email')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_hash');
            $table->string('email')->unique()->change();
        });
    }
};
