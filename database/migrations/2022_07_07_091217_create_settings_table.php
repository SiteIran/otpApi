<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('gift_percent')->default(10);
            $table->unsignedSmallInteger('discount_percent')->default(5);
            $table->timestamps();
        });

        \DB::table('settings')->insert([
            'gift_percent' => 10,
            'discount_percent' => 5,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
