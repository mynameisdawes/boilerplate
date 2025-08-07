<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_cart_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('identifier');
            $table->string('instance');
            $table->string('name')->nullable();

            $table->timestamps();
            $table->primary(['user_id', 'identifier', 'instance']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign(['identifier', 'instance'])->references(['identifier', 'instance'])->on('shop_cart')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('shop_cart_user');
    }
};
