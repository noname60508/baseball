<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class User extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // 名稱
            $table->string('name')->comment('名稱');
            // 帳號
            $table->string('account', 50)->comment('帳號');
            // 信箱
            $table->string('email')->nullable()->comment('信箱');
            // 密碼
            $table->string('password')->comment('密碼');
            // 身分組
            $table->integer('authority')->nullable()->comment('身分組');
            // 登入日期
            $table->dateTime('dt_login')->nullable()->comment('登入日期');
            // 登出日期
            $table->dateTime('dt_logout')->nullable()->comment('登出日期');
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
        Schema::drop('users');
    }
}
