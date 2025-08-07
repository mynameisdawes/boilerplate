<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('token', 64)->unique();
            $table->enum('type', ['login', 'registration'])->default('login');
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('expires_at_expiry')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('expires_at');
            $table->index('type');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_tokens');
    }
};
