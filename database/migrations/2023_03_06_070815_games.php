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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('比賽名稱');
            $table->string('enemyName')->nullable()->comment('敵對隊名');
            $table->date('date')->comment('日期');
            $table->string('location')->nullable()->comment('地點');
            $table->tinyint('result')->nullable()->comment('結果 0:輸 1:贏');
            $table->tinyint('HomeOrVisiting')->nullable()->comment('先/後功 0:後功 1:先功');
            $table->integer('homeScore')->nullable()->comment('後功隊伍分數');
            $table->integer('visitingScore')->nullable()->comment('先功隊伍分數');
            // 時間戳
            $table->timestamps();
            // 軟刪除
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('games');
    }
};
