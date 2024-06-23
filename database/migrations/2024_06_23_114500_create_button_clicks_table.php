<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('button_click', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('count');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('button_click');
    }
};
