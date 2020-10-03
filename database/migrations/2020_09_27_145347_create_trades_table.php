<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trade_info_id')->index();
            $table->unsignedBigInteger('buyer_id')->index();
            $table->decimal('amount', 10, 4)->comment("购买数量");
            $table->decimal('usd_amount', 10, 4)->comment("支付 USD 数量");
            $table->decimal('price', 10, 4)->comment("单价");
            $table->enum('trade_type', ['paypal'])->comment("交易方式");
            $table->string('trade_account')->comment('收款账户');
            $table->enum('status', ['pending', 'paid', 'confirmed', 'canceled'])->default('pending')
                ->comment("交易状态：等待中，已支付、已确认、已取消");
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
        Schema::dropIfExists('trades');
    }
}
