<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUseScreenToItemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->unsignedInteger('use_screen')
                ->after('item_type')
                ->default(12)  // 4:作業、8:承認
                ->comment('画面表示設定[BIT演算]（1:依頼、2:割振、4:作業、8:承認、16:納品、32:レポート）');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->dropColumn('use_screen');
        });
    }
}
