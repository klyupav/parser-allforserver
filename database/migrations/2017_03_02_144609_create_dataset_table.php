<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_set', function(Blueprint $table){
            $table->increments('id');
            $table->integer('exported')->default(0);
            $table->string('hash', 32)->unique();
            $table->timestamps();
            $table->integer('donor_id');
            $table->index('donor_id')
                ->references('id')->on('donors')
                ->onDelete('cascade');
            $table->string('source');
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
