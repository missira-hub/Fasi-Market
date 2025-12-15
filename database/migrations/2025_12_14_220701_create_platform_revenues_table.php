<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/..._create_platform_revenues_table.php
public function up()
{
    Schema::create('platform_revenues', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        $table->decimal('amount', 10, 2); // e.g., 10.00 TRY
        $table->string('currency')->default('try');
        $table->string('status')->default('paid'); // paid, pending, refunded
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('platform_revenues');
}
};
