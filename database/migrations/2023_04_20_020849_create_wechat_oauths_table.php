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
        Schema::create('wechat_oauths', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->comment("关联用户id")->nullable();
            $table->string("type")->comment("平台类型");
            $table->string("unionid")->nullable();
            $table->string("openid");
            $table->string("nickname")->nullable();
            $table->string("avatar")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wechat_oauths');
    }
};
