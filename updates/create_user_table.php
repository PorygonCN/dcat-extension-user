<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable  extends Migration
{
    // 这里可以指定你的数据库连接
    public function getConnection()
    {
        return config('database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("uid")->nullable()->after("id");
            $table->string('no')->comment("平台编号")->nullable()->after("uid");
            $table->boolean('enable')->comment("启用")->default(true)->after("no");
            $table->string("nickname")->nullable()->after("name");
            $table->string("username")->comment("账号")->nullable()->after("nickname");
            $table->string("mobile")->nullable()->after("username");
            $table->boolean('change_password')->comment("是否需要修改密码")->default(true)->after("password");
            $table->foreignId('department_id')->comment("部门")->nullable();
            $table->softDeletes();
            $table->dropUnique(["email"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("uid");
            $table->dropColumn("no");
            $table->dropColumn("enable");
            $table->dropColumn("nickname");
            $table->dropColumn("username");
            $table->dropColumn("mobile");
            $table->dropColumn("change_password");
            $table->dropSoftDeletes();
            $table->unique("email");
        });
    }
};
