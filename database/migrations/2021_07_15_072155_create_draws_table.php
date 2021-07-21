<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('draws', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('admin_id');
            $table->string('chat_id', 100)->nullable(true);
            $table->string('chat_title', 100)->nullable(true);
            $table->integer('message_id')->nullable(true);
            $table->integer('edit_message_id')->nullable(true);
            $table->string('text')->default('Старт розыгрыша на канале!');
            $table->string('text_btn')->default('(0) Участвовать');


            $table->integer('new_part')->default(0);
            $table->integer('count_part')->default(0);
            $table->integer('count_victory')->default(1);


            $table->string('pay_key', 150)->nullable(true);


            $table->integer('public')->default(0);
            $table->string('status')->default('Не опубликован');
            $table->timestamp('date_finish')->nullable(true);
            $table->timestamps();
            $table->timestamp('published_at')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('draws');
    }
}
