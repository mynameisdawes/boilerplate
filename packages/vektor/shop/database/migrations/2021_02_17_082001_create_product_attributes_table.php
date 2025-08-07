<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->string('value_label');
            $table->json('configuration')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_id']);

            // Adding composite indexes
            $table->index(['product_id', 'attribute_id']);
            $table->index(['attribute_id', 'value']);
            $table->index(['value', 'value_label']);
            $table->index(['product_id', 'attribute_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('product_attributes');
    }
};
