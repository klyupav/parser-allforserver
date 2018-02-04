<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInDataSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_set', function (Blueprint $table){
            $table->string('manufacturer')->nullable();
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->string('stockin')->nullable();
            $table->float('price_rub')->nullable();
            $table->float('price_usd')->nullable();
            $table->text('description')->nullable();
            $table->string('main_image')->nullable();
            $table->text('gallery')->nullable();
            $table->text('product_attributes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
