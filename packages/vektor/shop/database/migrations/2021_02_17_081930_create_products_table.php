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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(0)->index();
            $table->string('slug')->unique()->index();
            $table->string('name');
            $table->string('name_label');
            $table->string('sku')->nullable();
            $table->string('supplier_sku')->nullable();
            $table->decimal('price');
            $table->decimal('weight')->default(0);
            $table->json('images')->nullable();
            $table->json('configuration')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
