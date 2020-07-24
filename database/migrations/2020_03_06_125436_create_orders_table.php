<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->index();
            $table->unsignedBigInteger('seller_id')->index();
            $table->enum('sale_way', ['direct', 'auction'])->default('direct')->comment('出售方式：直接出售、拍卖');
            $table->decimal('total_amount', 10, 4)->comment("订单总价");
            $table->enum('pay_method', ['art_coin'])->nullable()->comment('支付方式');
            $table->enum('status', [
                'pending', 'paying', 'paid', 'pay_failed', 'refunding', 'refund', 'refund_failed', 'complete', 'cancel'
            ])
                ->default('pending')
                ->comment('订单状态：未支付、支付中、已支付、支付失败、退款中、已退款、退款失败, 订单完成、 订单取消');
            $table->unsignedBigInteger('order_pay_id')->nullable();
            $table->unsignedBigInteger('order_refund_id')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
