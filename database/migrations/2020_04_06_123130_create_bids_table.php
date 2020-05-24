<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auction_id')->comment('拍卖ID');
            $table->unsignedBigInteger('user_id')->comment('竞拍者ID');
            $table->decimal('bid_price', 10, 4)->comment('竞拍价格');
            $table->boolean('locked')->comment('竞拍金额是否锁定');
            $table->timestamp('bid_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bids');
    }
}
