<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestWorkIdToRequestAdditionalInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_additional_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('request_work_id')->nullable()->after('request_id')->comment('依頼作業ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_additional_infos', function (Blueprint $table) {
            $table->dropColumn('request_work_id');
        });
    }
}
