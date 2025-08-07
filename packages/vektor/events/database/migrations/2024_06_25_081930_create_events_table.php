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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->date('date');
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->string('type')->nullable();
            $table->longText('description')->nullable();
            $table->string('performance_title')->nullable();
            $table->string('performance_href')->nullable();
            $table->boolean('is_featured')->default(0);
            $table->tinyInteger('status')->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamp('scheduled_at')->useCurrent();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
