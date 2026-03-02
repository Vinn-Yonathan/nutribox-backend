<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string("name", 100);
            $table->text("description")->nullable();
            $table->text("image_src");
            $table->unsignedInteger("stock")->default(0);
            $table->unsignedInteger("calories");
            $table->unsignedInteger("price");
            $table->boolean("is_featured")->default(false);
            $table->timestamps();

            $table->index("stock");
            $table->index("is_featured");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
