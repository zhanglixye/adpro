<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00016Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00016Seeder
     *
     * @return void
     */
    public function run()
    {
        // ==============================
        // value settings
        // ==============================

        // business
        $company_id = 1;  // ◆ 企業ID
        $business = [
            'id' => 16,  // ◆ 業務ID
            'name' => 'LINEキャプチャ取得(メッセージ)',  // ◆ 業務名
            'description' => '対象アカウントのメッセージのADとLPをキャプチャ取得する',  // ◆ 業務の説明
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),  // 固定
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),  // 固定
            'reports' => [
                [
                    'name' => '作業レポート',  // ◆ レポート識別名
                    'description' => '<h4>[LINEキャプチャ取得]作業レポート</h4><br>配信年月を選択してください。<br>対象の配信年月のレポートを出力します。',  // ◆ レポート説明
                    'identifier' => 'lineCaptureReport'  // ◆ 呼び出し関数名
                ],
            ],
        ];

        // step
        $steps = [
            [
                'id' => 22,  // ◆ 作業ID
                'name' => 'キャプチャ取得(メッセージ)',  // ◆ 作業名
                'description' => '対象アカウントのメッセージのADとLPをキャプチャ取得する',  // ◆ 作業の説明
                'next_id' => 24,  // ◆ 次に続く作業があれば次の作業ID、無ければnull
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),  // ◆ 自動割振ならACTIVE、手動割振ならINACTIVE
                'is_auto_approval' => \Config::get('const.FLG.ACTIVE'),  // ◆ 自動承認ならACTIVE、手動承認ならINACTIVE
                'allocation_parallel' => 1,  // ◆ 自動割振人数
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),  // ◆ 作業依頼通知設定
                'request_screen_template' => '{"mail":{"header":"<div style=\"border: 1px grey dashed;font-size: larger;padding: 8px;\"><div><p>下記の最終取得配信日より新しい配信があれば、右に「配信日」と「配信時間」を入力してください。配信が無い場合は「配信なし」を選択してください。</p><div>取得したAD・LPを登録してください。</div><ul style=\"list-style: disc;margin: 0;\"><li>各LPのAD位置はルールに則って入力してください</li><li>piiicで加工できなかった場合、備考欄に入力してください</li><li>新規追加アカウントの場合、アイコンも登録してください</li></ul></div></div>","footer":""}}',  // 依頼内容の画面テンプレート
                'to_order_detail' => [
                    "order_details" => [
                        [
                            "id" =>  null,
                            "item_key" =>  "最新取得配信日時(メッセージ)",
                            "item_type" =>  0,
                            "value_type" =>  1,
                            "value" =>  "C00100_24",
                            "ignore_empty_string" =>  true,
                            "conditions" =>  0
                        ],
                        [
                            "id" =>  null,
                            "item_key" =>  "キャプチャ取得作業日(メッセージ)",
                            "item_type" =>  0,
                            "value_type" =>  2,
                            "value" =>  "Asia/Tokyo",
                            "ignore_empty_string" =>  false,
                            "conditions" =>  0
                        ]
                    ]
                ],
                'type' => \Config::get('const.STEP_TYPE.INPUT'),  // 固定
                'time_unit' => 3,  // 固定
                'deadline_limit' => 1,  // 固定
                'end_criteria' => 0,  // 固定
                'allocation_method' => 2,  // 固定
                'approval_conditions' => 1,  // 固定
                'split_items' => null,  // 作業を画面入力項目で分割する場合に設定

                // ◆ 以下、task_results.contentの内容を承認画面表示用に設定する
                'item_configs' => [
                    "results" => ["label" => "処理結果", "type" => null, "sort" => -1, "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))],  // 固定
                    "results/type" => ["label" => null, "type" => 10, "sort" => 0, "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))],  // 固定
                    "C00100_26" => [
                        "label" => "カテゴリ",
                        "type" => 100,
                        "sort" => 1,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                    "C00100_22" => [
                        "label" => "アカウント",
                        "type" => 100,
                        "sort" => 2,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                    "C00100_24" => [
                        "label" => "最新取得配信日時",
                        "type" => 100,
                        "sort" => 3,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))
                    ],
                    "G00000_1" => [
                        "label" => "キャプチャ",
                        "type" => null,
                        "sort" => 20,
                    ],
                    "G00000_1/C00100_21" => [
                        "label" => "配信日時",
                        "type" => 100,
                        "sort" => 21,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))
                    ],
                    "G00000_1/C00100_28" => [
                        "label" => "配信日時",  // 表示用：G00000_1/C00100_21を("m月d日(曜日)h:mm"形式)にした値
                        "type" => 100,
                        "sort" => 22,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                    "G00000_1/C00800_18" => [
                        "label" => "AD画像素材",
                        "type" => 800,
                        "sort" => 23,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))
                            + pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                    "G00000_1/C00800_19" => [
                        "label" => "LP画像素材",
                        "type" => 800,
                        "sort" => 24,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))
                            + pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                    "G00000_1/C00100_27" => [
                        "label" => "LP数",
                        "type" => 100,
                        "sort" => 25,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))
                            + pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                    "G00000_1/C00200_8" => [
                        "label" => "備考",
                        "type" => 200,
                        "sort" => 26,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))
                            + pow(2, \Config::get('const.PROCESS_TYPE.DELIVERY'))
                    ],
                ],
            ],
            [
                'id' => 24,  // ◆ 作業ID
                'name' => 'PPT編集',  // ◆ 作業名
                'description' => '取得したキャプチャを使用し、配信事例PPTを作成する',  // ◆ 作業の説明
                'next_id' => null,  // ◆ 次に続く作業があれば次の作業ID、無ければnull
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),  // ◆ 自動割振ならACTIVE、手動割振ならINACTIVE
                'is_auto_approval' => \Config::get('const.FLG.ACTIVE'),  // ◆ 自動承認ならACTIVE、手動承認ならINACTIVE
                'allocation_parallel' => 1,  // ◆ 自動割振人数
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),  // ◆ 作業依頼通知設定
                'request_screen_template' => '{"before_work":{"header":"<div style=\"border: 1px grey dashed;font-size: larger;padding: 8px;\"><div><p>下記のドライブより前回配信分のスライドをダウンロードし、編集してください。<br>新規追加アカウントの場合、下記よりフォーマットをダウンロードし、作成してください。</p><div>配信日時の編集を忘れずに行ってください。</div><ul style=\"list-style: disc;margin: 0;\"><li>下記の配信日時からコピペすること。</li></ul></div></div>","footer":"<hr><div style=\"font-size: larger;\"><div style=\"text-align: center;\"><p><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"https:\/\/drive.google.com\/drive\/folders\/1hUX9tqh9lhqgU3pR5RUv1Ri4Aiv4aGCC\" style=\"text-decoration: underline;\">Googleドライブを開く</a></p><p><a target=\"_blank\" rel=\"noopener noreferrer\" href=\"https:\/\/drive.google.com\/drive\/folders\/1vjS3Wr9yGD-RTqHO8NrGtfMEqbAw9EiW\" style=\"text-decoration: underline;\">PPTフォーマットをダウンロード</a></p></div></div>"}}',  // 依頼内容の画面テンプレート
                'to_order_detail' => [
                    "order_details" => [
                        [
                            "id" => null,
                            "item_key" => "最新PPT作成配信日時(メッセージ)",
                            "item_type" => \Config::get('const.TO_ORDER_DETAIL.ITEM_TYPE.ORDER_DETAIL_COLUMN'),
                            "value_type" => \Config::get('const.TO_ORDER_DETAIL.VALUE_TYPE.REQUEST_WORK_CODE'),
                            "value" => "",
                            "ignore_empty_string" => true,
                            "conditions" => \Config::get('const.TO_ORDER_DETAIL.CONDITIONS.ABOVE'),
                        ],
                    ]
                ],
                'type' => \Config::get('const.STEP_TYPE.INPUT'),  // 固定
                'time_unit' => 3,  // 固定
                'deadline_limit' => 1,  // 固定
                'end_criteria' => 1,  // 作業終了判定基準（全体:0、グループ内:1、親グループ内:2）
                'allocation_method' => 2,  // 固定
                'approval_conditions' => 1,  // 固定
                'split_items' => 'G00000_1/C00100_21',  // 作業を画面入力項目で分割する場合に設定

                 // ◆ 以下、task_results.contentの内容を承認画面表示用に設定する
                'item_configs' => [
                "results" => ["label" => "処理結果", "type" => null, "sort" => -1, "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))],  // 固定
                "results/type" => ["label" => null, "type" => 10, "sort" => 0, "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL'))],  // 固定
                    "G00000_1" => [
                        "label" => null,
                        "type" => 0,
                        "sort" => 1,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => null,
                        "layout_option" => '{"layout":1,' .
                                            ' "urls": [{' .
                                            ' "label": "マニュアルを参照する",' .
                                            ' "url": "manuals/B00016/S00024/マニュアルを参照する.pdf"' .
                                            ' }]' .
                                            '}',
                        "validate_option" => null
                    ],
                    "G00000_2" => [
                        "label" => 'STEP１：作業内容に問題が無いか確認してください',
                        "type" => 0,
                        "sort" => 2,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'G00000_1',
                        "layout_option" => '{"layout": 1}',
                        "validate_option" => null
                    ],
                    "C00400_3" => [
                        "label" => '配信日時・曜日は左の内容と合っていますか',
                        "type" => 400,
                        "sort" => 3,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 1,
                        "parent_id" => 'G00000_2',
                        "layout_option" => '{"value":false}',
                        "validate_option" => null
                    ],
                    "C00400_4" => [
                        "label" => '右矢印▶は正しく配置されていますか　(LPがない場合は削除)',
                        "type" => 400,
                        "sort" => 4,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 1,
                        "parent_id" => 'G00000_2',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "C00400_5" => [
                        "label" => 'LPが同じものは１つにまとめました',
                        "type" => 400,
                        "sort" => 5,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 1,
                        "parent_id" => 'G00000_2',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "C00400_6" => [
                        "label" => '貼り付け位置、書式に間違いはありません',
                        "type" => 400,
                        "sort" => 6,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 1,
                        "parent_id" => 'G00000_2',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "G00000_7" => [
                        "label" => '以下の場合はマニュアルを確認してください。',
                        "type" => 0,
                        "sort" => 7,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'G00000_2',
                        "layout_option" => '{"layout": 1}',
                        "validate_option" => null
                    ],
                    "C01000_8" => [
                        "label" => '・ADが長くて1列におさまらない',
                        "type" => 1000,
                        "sort" => 8,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'G00000_7',
                        "layout_option" => '{"value":"manuals/B00016/S00024/ADが長くて1列におさまらない.pdf"}',
                        "validate_option" => null
                    ],
                    "C01000_9" => [
                        "label" => '・LPが多くスライドが2枚以上になる',
                        "type" => 1000,
                        "sort" => 9,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'G00000_7',
                        "layout_option" => '{"value":"manuals/B00016/S00024/LPが多くスライドが2枚以上になる.pdf"}',
                        "validate_option" => null
                    ],
                    "C01000_10" => [
                        "label" => '・新規追加アカウントの場合',
                        "type" => 1000,
                        "sort" => 10,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'G00000_7',
                        "layout_option" => '{"value":"manuals/B00016/S00024/新規追加アカウントの場合.pdf"}',
                        "validate_option" => null
                    ],
                    "C00400_11" => [
                        "label" => 'マニュアルを確認しました',
                        "type" => 400,
                        "sort" => 11,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 1,
                        "parent_id" => 'G00000_2',
                        "layout_option" => '{"value": false}',
                        "validate_option" => null
                    ],
                    "C00400_16" => [
                        "label" => 'PPTを格納しました',
                        "type" => 400,
                        "sort" => 12,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 1,
                        "parent_id" => 'G00000_2',
                        "layout_option" => '{"value": false}',
                        "validate_option" => null
                    ],
//                     "G00000_12" => [
//                         "label" => 'STEP２：格納先のURLを入力してください。',
//                         "type" => 0,
//                         "sort" => 12,
//                         "is_required" => 1,
//                         "parent_id" => 'G00000_1',
//                         "layout_option" => '{"layout": 1}',
//                         "validate_option" => null
//                     ],
//                     "C00100_13" => [
//                         "label" => null,
//                         "type" => 100,
//                         "sort" => 13,
//                         "is_required" => 1,
//                         "parent_id" => 'G00000_12',
//                         "layout_option" => null,
//                         "validate_option" => '{"max_length_char":200,"format_pattern":""}'
//                     ],
//                     "G00000_14" => [
//                         "label" => 'STEP３：PPTをアップロードしてください。',
//                         "type" => 0,
//                         "sort" => 14,
//                         "is_required" => 0,
//                         "parent_id" => 'G00000_1',
//                         "layout_option" => '{"layout": 1}',
//                         "validate_option" => null
//                     ],
//                     "C00800_15" => [
//                         "label" => null,
//                         "type" => 800,
//                         "sort" => 15,
//                         "is_required" => 1,
//                         "parent_id" => 'G00000_14',
//                         "layout_option" => null,
//                         "validate_option" => null
//                     ],
                ],
            ],
        ];

        // const
        $language_id = 2;
        $datetime_now = Carbon::now();
        $system_user_id = 0;

        $created_columns = [
            'created_at' => $datetime_now,
            'created_user_id' => $system_user_id
        ];
        $updated_columns = [
            'updated_at' => $datetime_now,
            'updated_user_id' => $system_user_id
        ];

        // ==============================
        // save master data
        // ==============================

        \DB::beginTransaction();
        try {
            // ==============================
            // business and step settings
            // ==============================
            if (\DB::table('businesses')->where('id', $business['id'])->doesntExist()) {
                \Db::table('businesses')->insert(
                    [
                        'id' => $business['id'],
                        'name' => $business['name'],
                        'company_id' => $company_id,
                        'description' => $business['description'],
                        'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                    ] + $created_columns + $updated_columns
                );
            } else {
                \Db::table('businesses')->where('id', $business['id'])->update(
                    [
                        'name' => $business['name'],
                        'company_id' => $company_id,
                        'description' => $business['description'],
                    ] + $updated_columns
                );
            }

            foreach ($steps as $step) {
                if (\DB::table('steps')->where('id', $step['id'])->doesntExist()) {
                    \DB::table('steps')->insert(
                        [
                            'id' => $step['id'],
                            'name' => $step['name'],
                            'step_type' => $step['type'],
                            'url' => sprintf('b%05d/s%05d', $business['id'], $step['id']),
                            'description' => $step['description'],
                            'time_unit' => $step['time_unit'],
                            'deadline_limit' => $step['deadline_limit'],
                            'end_criteria' => $step['end_criteria'],
                            'is_send_task_req_mail' => $step['is_send_task_req_mail'],
                            'request_screen_template' => $step['request_screen_template'],
                            'to_order_detail' => is_null($step['to_order_detail']) ? null : json_encode($step['to_order_detail']),
                            'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('steps')->where('id', $step['id'])->update(
                        [
                            'name' => $step['name'],
                            'step_type' => $step['type'],
                            'description' => $step['description'],
                            'time_unit' => $step['time_unit'],
                            'deadline_limit' => $step['deadline_limit'],
                            'end_criteria' => $step['end_criteria'],
                            'is_send_task_req_mail' => $step['is_send_task_req_mail'],
                            'request_screen_template' => $step['request_screen_template'],
                            'to_order_detail' => is_null($step['to_order_detail']) ? null : json_encode($step['to_order_detail']),
                        ] + $updated_columns
                    );
                }

                if (\DB::table('business_flows')->where('step_id', $step['id'])->doesntExist()) {
                    \DB::table('business_flows')->insert(
                        [
                            'step_id' => $step['id'],
                            'seq_no' => 1,
                            'business_id' => $business['id'],
                            'next_step_id' => $step['next_id'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('business_flows')->where('step_id', $step['id'])->update(
                        [
                            'business_id' => $business['id'],
                            'next_step_id' => $step['next_id'],
                        ] + $updated_columns
                    );
                }

                if (\DB::table('allocation_configs')->where('step_id', $step['id'])->doesntExist()) {
                    \DB::table('allocation_configs')->insert(
                        [
                            'step_id' => $step['id'],
                            'parallel' => $step['allocation_parallel'],
                            'is_auto' => $step['is_auto_allocation'],
                            'method' => $step['allocation_method'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('allocation_configs')->where('step_id', $step['id'])->update(
                        [
                            'parallel' => $step['allocation_parallel'],
                            'is_auto' => $step['is_auto_allocation'],
                            'method' => $step['allocation_method'],
                        ] + $updated_columns
                    );
                }

                if (\DB::table('approval_configs')->where('step_id', $step['id'])->doesntExist()) {
                    \DB::table('approval_configs')->insert(
                        [
                            'step_id' => $step['id'],
                            'is_auto' => $step['is_auto_approval'],
                            'conditions' => $step['approval_conditions'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('approval_configs')->where('step_id', $step['id'])->update(
                        [
                            'is_auto' => $step['is_auto_approval'],
                            'conditions' => $step['approval_conditions'],
                        ] + $updated_columns
                    );
                }

                if ($step['split_items']) {  // split_itemsの指定がある場合のみ
                    if (\DB::table('create_request_work_configs')->where('step_id', $step['id'])->doesntExist()) {
                        \DB::table('create_request_work_configs')->insert(
                            [
                                'step_id' => $step['id'],
                                'split_items' => $step['split_items'],
                                'code' => '>',
                            ] + $created_columns + $updated_columns
                        );
                    } else {
                        \DB::table('create_request_work_configs')->where('step_id', $step['id'])->update(
                            [
                                'split_items' => $step['split_items'],
                                'code' => '>',
                            ] + $updated_columns
                        );
                    }
                }
            }

            // ==============================
            // custom report settings
            // ==============================
            foreach ($business['reports'] as $report) {
                $businesses_reports = \DB::table('reports')
                    ->join('businesses_reports', 'businesses_reports.report_id', '=', 'reports.id')
                    ->where('businesses_reports.business_id', $business['id'])
                    ->where('reports.identifier', $report['identifier']);

                if ($businesses_reports->exists()) {
                    $report_id = $businesses_reports->value('reports.id');
                    \DB::table('reports')->where('reports.id', $report_id)->update(
                        [
                            'name' => $report['name'],
                            'description' => $report['description'],
                            'is_deleted' => \Config::get('const.FLG.INACTIVE')
                        ] + $updated_columns
                    );
                } else {
                    $report_id = \DB::table('reports')->insertGetId(
                        [
                            'name' => $report['name'],
                            'description' => $report['description'],
                            'identifier' => $report['identifier']
                        ] + $created_columns + $updated_columns
                    );

                    \DB::table('businesses_reports')->insert(
                        [
                            'business_id' => $business['id'],
                            'report_id' => $report_id
                        ] + $created_columns + $updated_columns
                    );
                }
            }

            // ==============================
            // client config settings
            // ==============================
            if (\DB::table('guest_client_issue_configs')->where('business_id', $business['id'])->doesntExist()) {
                \DB::table('guest_client_issue_configs')->insert(
                    [
                        'business_id' => $business['id'],
                        'is_auto' => $business['is_auto_sending_client_page'],
                    ] + $created_columns + $updated_columns
                );
            } else {
                \DB::table('guest_client_issue_configs')->where('business_id', $business['id'])->update(
                    [
                        'is_auto' => $business['is_auto_sending_client_page'],
                    ] + $updated_columns
                );
            }

            if (\DB::table('client_default_configs')->where('business_id', $business['id'])->doesntExist()) {
                \DB::table('client_default_configs')->insert(
                    [
                        'business_id' => $business['id'],
                        'is_viewable_request_related_mails' => $business['is_viewable_in_client_page'],
                        'is_viewable_request_additional_infos' => $business['is_viewable_in_client_page'],
                    ] + $created_columns + $updated_columns
                );
            } else {
                \DB::table('client_default_configs')->where('business_id', $business['id'])->update(
                    [
                        'is_viewable_request_related_mails' => $business['is_viewable_in_client_page'],
                        'is_viewable_request_additional_infos' => $business['is_viewable_in_client_page'],
                    ] + $updated_columns
                );
            }

            // ==============================
            // item config settings
            // ==============================
            foreach ($steps as $step) {
                \DB::table('item_configs')->where('step_id', $step['id'])->update(
                    ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
                );
                $item_config_id_array = [];
                foreach ($step['item_configs'] as $key => $obj) {
                    $label_id = null;
                    $diff_check_level = [];// diff_check_level専用の配列
                    if (array_key_exists('diff_check_level', $obj)) {
                        $diff_check_level = [
                            'diff_check_level' => $obj['diff_check_level']
                        ];
                    }

                    if (array_key_exists('parent_id', $obj)) {
                        if (isset($obj['label'])) {
                            $label_id = \DB::table('labels')->insertGetId(
                                [
                                    'language_id' => $language_id,
                                    'name' => $obj['label'],
                                ] + $created_columns + $updated_columns
                            );
                        }
                        if (!empty($obj['layout_option'])) {
                            $layout_option_array = json_decode($obj['layout_option'], true);
                            if (!empty($layout_option_array['urls'])) {
                                foreach ($layout_option_array['urls'] as &$url) {
                                    if (!empty($url['label'])) {
                                        $layout_url_label_id = \DB::table('labels')->insertGetId(
                                            [
                                                'language_id' => $language_id,
                                                'name' => $url['label'],
                                            ] + $created_columns + $updated_columns
                                        );
                                        $url['label_id'] = $layout_url_label_id;
                                        unset($url['label']);
                                    }
                                }
                                $obj['layout_option'] = json_encode($layout_option_array);
                            }
                        }
                        $parent_id = null;
                        if (!empty($obj['parent_id'])) {
                            $parent_id = $item_config_id_array[$obj['parent_id']];
                        }
                        $item_config_id = \DB::table('item_configs')->insertGetId(
                            [
                                'step_id' => $step['id'],
                                'sort' => $obj['sort'],
                                'label_id' => $label_id,
                                'item_key' => $key,
                                'item_type' => $obj['type'],
                                'use_screen' => $obj['use_screen'] ?? pow(2, \Config::get('const.PROCESS_TYPE.WORK')) + pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL')),
                                'is_required' => $obj['is_required'],
                                'parent_id' => $parent_id,
                                'layout_option' => $obj['layout_option'],
                                'validate_option' => $obj['validate_option']
                            ] + $created_columns + $updated_columns + $diff_check_level
                        );

                        $item_config_id_array[$key] = $item_config_id;
                    } else {
                        if (isset($obj['label'])) {
                            $label_id = \DB::table('labels')->insertGetId(
                                [
                                    'language_id' => $language_id,
                                    'name' => $obj['label'],
                                ] + $created_columns + $updated_columns
                            );
                        }
                        $item_config_id = \DB::table('item_configs')->insertGetId(
                            [
                                'step_id' => $step['id'],
                                'sort' => $obj['sort'],
                                'label_id' => $label_id,
                                'item_key' => $key,
                                'item_type' => $obj['type'],
                                'use_screen' => $obj['use_screen'] ?? pow(2, \Config::get('const.PROCESS_TYPE.WORK')) + pow(2, \Config::get('const.PROCESS_TYPE.APPROVAL')),
                            ] + $created_columns + $updated_columns + $diff_check_level
                        );
                        if (isset($obj['values'])) {
                            foreach ($obj['values'] as $sort => $label) {
                                $label_id = \DB::table('labels')->insertGetId(
                                    [
                                        'language_id' => $language_id,
                                        'name' => $label,
                                    ] + $created_columns + $updated_columns
                                );
                                \DB::table('item_config_values')->insert(
                                    [
                                        'item_config_id' => $item_config_id,
                                        'sort' => $sort,
                                        'label_id' => $label_id,
                                    ] + $created_columns + $updated_columns
                                );
                            }
                        }
                    }
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
        }
    }
}
