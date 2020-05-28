<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_pays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string("origin_tx_no")->unique()->comment('原始单号（区块链交易号）');
            $table->timestamp("pay_at")->nullable()->comment('支付成功时间');
            $table->string('tx_msg')->nullable();
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
        Schema::dropIfExists('order_pays');
    }
}
