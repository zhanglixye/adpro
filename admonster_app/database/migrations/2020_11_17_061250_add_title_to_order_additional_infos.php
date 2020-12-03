<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleToOrderAdditionalInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_additional_infos', function (Blueprint $table) {
            //
            $table->longText('title')
                ->after('order_detail_id')
                ->nullable()
                ->comment('タイトル');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_additional_infos', function (Blueprint $table) {
            //
            $table->dropColumn('title');
        });
    }
}
