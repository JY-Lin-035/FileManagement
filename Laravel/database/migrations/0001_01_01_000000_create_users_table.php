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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('name', 128)->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->bigInteger('signalFileSize')->default(524288000); // Default 500MB
            $table->bigInteger('totalFileSize')->default(10737418240); // Default 10GB
            $table->bigInteger('usedSize')->default(0)->nullable(); // Default 0
            $table->smallInteger('identity')->default(0); // 0: User, 1: Admin
            $table->boolean('enable')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('deleteDate')->nullable();
            $table->timestamp('lastSignInDate')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('share_links', function (Blueprint $table) {
            $table->id()->primary();
            // $table->unsignedBigInteger('ownerId');
            $table->string('path');
            $table->string('fileName')->nullable();
            $table->string('link')->unique();
            $table->timestamps();

            $table->foreignId('owner_id')->constrained('accounts')->cascadeOnDelete();

            // $table->foreign('ownerId')->references('id')->on('accounts')->onDelete('cascade');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
