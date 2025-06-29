<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiLogsTable extends Migration
{
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('endpoint', 2048);
            $table->dateTime('timestamp');
            $table->float('duration_ms');
            $table->integer('status_code');
            $table->string('user_agent', 512)->nullable();
            $table->string('ip', 45)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');
    }
}
