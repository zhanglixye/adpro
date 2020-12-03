<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\BusinessFlow;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Step;
use App\Models\Label;
use App\Models\ItemConfig;

class StepsController extends Controller
{
    public function index(Request $req)
    {
        $user = \Auth::user();
        // 作業マスタ一覧情報を取得
        $steps = Step::select()
            ->where('is_deleted', config('const.FLG.INACTIVE'))
            ->orderBy('id')
            ->get();
        // 全ユーザ情報を取得
        $candidates = User::select(
            'id',
            'name',
            'user_image_path'
        )->get();
        // ラベルデータリストを取得
        $labels = Label::getLangKeySetAll();
        return response()->json([
            'list' => $steps,
            'candidates' => $candidates,
            'labels'=> $labels,
        ]);
    }

    public function getItemConfigs(Request $req)
    {
        // 作業ID
        $step_id = $req->step_id;
        // 前工程があれば取得する
        $before_step_ids = BusinessFlow::beforeStepIds($step_id);
        // 作業項目設定リストを取得
        $item_configs = ItemConfig::whereIn('step_id', $before_step_ids)
            ->selectRaw(
                'id,'.
                'step_id,'.
                'item_key,'.
                'item_type,'.
                'label_id,'.
                'use_screen>> ' . \Config::get('const.PROCESS_TYPE.DELIVERY') . ' & 1 as use_screen_type_delivery_flag'
            )
            ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
            // 候補から除外する項目
            ->whereNotNull('item_type')
            ->whereNotIn('item_type', [
                \Config::get('const.ITEM_CONFIG_TYPE.BOX'),
                \Config::get('const.ITEM_CONFIG_TYPE.TASK_RESULT'),
                \Config::get('const.ITEM_CONFIG_TYPE.CHECKBOX'),
                \Config::get('const.ITEM_CONFIG_TYPE.RADIO'),
                \Config::get('const.ITEM_CONFIG_TYPE.LINK')
            ])
            // 候補から除外する項目/
            ->orderBy('sort')
            ->get();

        return response()->json([
            'list'=> $item_configs,
        ]);
    }

    public function updateRequestTemplate(Request $req)
    {
        $user = \Auth::user();
        $step_id = $req->input('step_id');
        $template = json_decode($req->input('template'));
        $item_configs = json_decode($req->input('item_configs'));
        $use_screen_decimal_type_delivery = pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'));

        // TODO: 排他制御

        // DB登録
        \DB::beginTransaction();
        try {
            // 作業の更新
            $step = Step::find($step_id);
            $step->request_screen_template = json_encode($template, JSON_UNESCAPED_UNICODE);
            $step->updated_user_id = $user->id;
            $step->save();

            // 更新対象IDを保持する変数を宣言しておく
            $updated_item_config_ids = [];

            // 表示設定の更新
            foreach ($item_configs as $after_item) {
                $before_item = ItemConfig::find($after_item->id);
                $before_use_screen_flag = $before_item->use_screen>> \Config::get('const.PROCESS_TYPE.DELIVERY') & 1;
                $after_use_screen_flag = $after_item->use_screen_type_delivery_flag;
                // 変更があればitem_configs.idを控えておく
                if ($before_use_screen_flag !== $after_use_screen_flag) {
                    $updated_item_config_ids[] = $after_item->id;
                }
            }

            if ($updated_item_config_ids) {
                // bulkupdate
                \DB::update(
                    'UPDATE item_configs'
                    .' SET use_screen = '
                    .'   CASE use_screen>> '.\Config::get('const.PROCESS_TYPE.DELIVERY').' & 1'
                    .'     WHEN 0 THEN use_screen + '.$use_screen_decimal_type_delivery
                    .'     WHEN 1 THEN use_screen - '.$use_screen_decimal_type_delivery
                    .'  END'
                    .' , updated_user_id = '.$user->id
                    .' , updated_at = "'.Carbon::now().'"'
                    .' WHERE id IN ('.implode(',', $updated_item_config_ids).');'
                );
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'result' => 'error',
            ]);
        }
        return response()->json([
            'result' => 'success',
            'step' => $step
        ]);
    }
}
