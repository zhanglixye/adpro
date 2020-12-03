<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCustomStatusAttributeIdToOrderDetailsRelatedCustomStatusAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details_related_custom_status_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_status_attribute_id')->nullable()->change();

            // テーブルコメント定義
            $table_comment = '案件明細に関連するカスタムステータスの属性\n'
                .'未選択（初期）　->　レコード無し\n'
                .'未選択に変更　->　レコードあり\n'
                .'選択　->　レコードあり\n'
                .'カスタムステータス　削除:true　->　レコードあり\n'
                .'カスタムステータス（属性） 削除:true ->　未選択と同じ';
            DB::statement("ALTER TABLE order_details_related_custom_status_attributes COMMENT '{$table_comment}'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details_related_custom_status_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_status_attribute_id')->nullable(false)->change();

            // テーブルコメント定義
            $table_comment = '案件明細に関連するカスタムステータスの属性\n'
                .'未選択　->　レコード無し\n'
                .'選択　->　レコードあり\n'
                .'カスタムステータス　削除:true　->　レコードあり\n'
                .'カスタムステータス（属性） 削除:true ->　未選択と同じ';
            DB::statement("ALTER TABLE order_details_related_custom_status_attributes COMMENT '{$table_comment}'");
        });
    }
}
