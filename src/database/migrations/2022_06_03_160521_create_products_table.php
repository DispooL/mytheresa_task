<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('category_id')
                ->unsigned()
                ->foreign()
                ->references('id')
                ->on('categories');

            $table->integer('sku');
            $table->integer('original_price');

            $table->string('name');
            $table->string('currency');

            $table->index(['category_id', 'original_price']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
