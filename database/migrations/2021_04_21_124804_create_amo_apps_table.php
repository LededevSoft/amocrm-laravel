<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('client_secret');
            $table->text('client_id');
            $table->text('base_domain');
            $table->text('redirect_url');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires')->nullable();
            $table->mediumText('account_name')->nullable(true);
            $table->bigInteger('account_id')->nullable(true);
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_apps');
    }
}
