<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('bid_user_id')->nullable()->comment('当前竞标者ID');
            $table->decimal('start_price', 10, 4)->comment('起拍价');
            $table->decimal('current_price', 10, 4)->default(0)->comment('当前价格');
            $table->decimal('step_price', 10, 4)->comment('加价幅度');
            $table->decimal('fixed_price', 10, 4)->nullable()->comment('一口价');
            $table->decimal('purchase_price', 10, 4)->nullable()->comment('购买价格');
            $table->enum('status', ['initial', 'bidding', 'bid_expired', 'bid_success', 'fixed_success'])->default('initial')->comment('竞拍状态：初始化、竞拍中、竞拍失败、竞拍成功、一口价成功');
            $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->timestamp('end_at')->nullable()->comment('结束时间');
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
        Schema::dropIfExists('auctions');
    }
}
