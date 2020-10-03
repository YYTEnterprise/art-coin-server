<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trader_id')->index();
            $table->decimal('max_amount', 10, 4)->comment("最大数量");
            $table->decimal('max_usd_amount', 10, 4)->comment("最大 USD 数量");
            $table->decimal('min_usd_amount', 10, 4)->comment("最小 USD 数量");
            $table->decimal('price', 10, 4)->comment("单价");
            $table->enum('trade_type', ['paypal'])->default('paypal')->comment("交易方式");
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
        Schema::dropIfExists('trade_infos');
    }
}
