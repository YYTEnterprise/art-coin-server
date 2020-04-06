<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('title')->comment('拍卖标题');
            $table->string('description')->comment('竞拍说明');
            $table->unsignedBigInteger('starting_price')->comment('起拍价');
            $table->unsignedBigInteger('current_price')->comment('当前价格');
            $table->unsignedBigInteger('step_price')->comment('加价幅度');
            $table->unsignedBigInteger('fixed_price')->nullable()->comment('一口价');
            $table->unsignedBigInteger('price')->nullable()->comment('实际竞拍价格');
            $table->integer('big_period')->comment('竞价周期');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('ended_at')->nullable()->comment('结束时间');
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
        Schema::dropIfExists('auction');
    }
}
