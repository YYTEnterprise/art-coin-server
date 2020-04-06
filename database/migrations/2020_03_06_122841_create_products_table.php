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
            $table->string('type')->index();
            $table->unsignedBigInteger('category_id')->comment('商品类型');
            $table->unsignedBigInteger('deliver_type_id')->comment('交付方式');
            $table->unsignedBigInteger('tariff')->comment('关税说明');
            $table->string('title')->comment('产品名称');
            $table->text('brief_desc')->comment('产品详情');
            $table->text('detail_desc')->comment('详情描述');
            $table->string('cover_image')->comment('封面图片路径');
            $table->boolean('on_sale')->default(false)->comment('是否上架');
            $table->unsignedInteger('like_count')->default(0)->commnet('点赞数量');
            $table->decimal('price', 10, 2)->commnet('商品价格');
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
