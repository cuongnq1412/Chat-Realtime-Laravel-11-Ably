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
            $table->id('user_id'); // Đặt tên rõ ràng cho cột khóa chính
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('channels', function (Blueprint $table) {
            $table->id('channel_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('status');
            $table->text('address')->nullable();
            $table->rememberToken();
            $table->string('verification_code')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->onDelete('cascade')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('conversations', function (Blueprint $table) {

            $table->unsignedBigInteger('user1_id');
            $table->unsignedBigInteger('user2_id');
            $table->string('conversation_name')->unique();
            $table->timestamps();

            $table->foreign('user1_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('user2_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');

            $table->unsignedBigInteger('message_person');
            $table->text('content');
            $table->timestamps();
            $table->string('conversation_name');
            $table->foreign('conversation_name')->references('conversation_name')->on('conversations')->onDelete('cascade');

            $table->foreign('message_person', 'fk_message_person_user')
                  ->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation');
        Schema::dropIfExists('sessions');

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
