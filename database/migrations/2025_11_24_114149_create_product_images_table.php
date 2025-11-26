<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();

            // SỬA LỖI: Chuyển sang unsignedInteger để tương thích với Khóa Chính (id)
            // của bảng 'products' nếu nó là INT UNSIGNED.
            $table->unsignedInteger('product_id');

            // Thiết lập Khóa Ngoại đến bảng products
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade'); // Khi sản phẩm bị xóa, ảnh cũng bị xóa

            // Cột lưu trữ đường dẫn ảnh (được truy vấn trong ProductDataGrid)
            $table->string('path');
            $table->boolean('is_thumbnail')->default(false);
            // Cột tuỳ chọn để sắp xếp thứ tự hiển thị ảnh
            $table->integer('sort_order')->default(0); 

            $table->timestamps();

            // Đặt Index cho cột product_id để tối ưu hóa truy vấn JOIN
            $table->index('product_id');
            
            // Đảm bảo không có hai ảnh cùng đường dẫn cho cùng một sản phẩm
            $table->unique(['product_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};