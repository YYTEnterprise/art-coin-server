<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
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
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('stock_quantity')->default(1)->comment('库存数量');
            $table->string('title')->comment('产品名称');
            $table->text('brief_desc')->comment('产品详情');
            $table->text('detail_desc')->comment('详情描述');
            $table->string('cover_image')->comment('封面图片路径');
            $table->decimal('price', 10, 4)->nullable()->comment('商品价格');
            $table->enum('deliver_type', ['express', 'email'])->comment('交付方式: 实物-快递, 虚拟-邮件');
            $table->boolean('has_deliver_fee')->nullable()->comment('是否包含运费: true/false（实物产品必填）');
            $table->boolean('has_tariff')->nullable()->comment('是否包含关税: true/false（实物产品必填）');
            $table->string('deliver_remark')->nullable()->comment('交付说明（虚拟产品必填）');
            $table->boolean('on_sale')->default(false)->comment('是否上架');
            $table->enum('sale_way', ['direct', 'auction'])->default('direct')->comment('出售方式：直接出售、拍卖');
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
}
