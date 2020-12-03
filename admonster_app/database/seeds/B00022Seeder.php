<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00022Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00022Seeder
     *
     * @return void
     */
    public function run()
    {
        // ==============================
        // value settings
        // ==============================

        // business
        $company_id = 1;
        $business = [
            'id' => 22,
            'name' => '出稿調査（都度依頼）',
            'description' => 'どのサイトに、いつ、どの広告主が、どのくらい出稿していたかを調べること',
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),
        ];

        // step
        $steps = [
            [
                'id' => 29,
                'next_id' => null,
                'name' => '出稿調査（都度依頼）',
                'type' => \Config::get('const.STEP_TYPE.INPUT'),
                'description' => 'どのサイトに、いつ、どの広告主が、どのくらい出稿していたかを調べること',
                'time_unit' => 3,
                'deadline_limit' => 1,
                'end_criteria' => 0,
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),
                'allocation_parallel' => 1,
                'allocation_method' => 2,
                'approval_conditions' => 1,
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),
                'is_auto_approval' => \Config::get('const.FLG.INACTIVE'),
                'item_configs' => [
                    "results" => [
                        "label" => "処理結果",
                        "type" => null,
                        "sort" => -1,
                    ],
                    "results/type" => [
                        "label" => null,
                        "type" => 10,
                        "sort" => 0,
                    ],
                    "G00000_27" => [
                        "label" => "作成メール",
                        "type" => null,
                        "sort" => 27,
                    ],
                    "G00000_27/C00300_28" => [
                        "label" => "To",
                        "type" => 300,
                        "sort" => 28,
                    ],
                    "G00000_27/C00300_29" => [
                        "label" => "Cc",
                        "type" => 300,
                        "sort" => 29,
                    ],
                    "G00000_27/C00100_30" => [
                        "label" => "Subject",
                        "type" => 100,
                        "sort" => 30,
                    ],
                    "G00000_27/C00900_31" => [
                        "label" => "本文",
                        "type" => 900,
                        "sort" => 31,
                    ],
                    "G00000_27/C00800_32" => [
                        "label" => "ファイル添付",
                        "type" => 800,
                        "sort" => 32,
                    ],
                    "checklist" => [
                        "label" => "作業内容",
                        "type" => null,
                        "sort" => 100,
                        "is_required" => 0,
                        "parent_id" => null,
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0" => [
                        "label" => "作業チェック内容１",
                        "type" => 0,
                        "sort" => 101,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'checklist',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/0" => [
                        "label" => "調査期間が合っているか",
                        "type" => 400,
                        "sort" => 102,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/1" => [
                        "label" => "リンク先URLは開けるか",
                        "type" => 400,
                        "sort" => 103,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/2" => [
                        "label" => "有効期限の記入が正しいか",
                        "type" => 400,
                        "sort" => 104,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/3" => [
                        "label" => "連絡事項が合っているか",
                        "type" => 400,
                        "sort" => 105,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/3" => [
                        "label" => "全て出稿がなかった場合、「BOXよりご確認をお願い致します。」を削除したのか。",
                        "type" => 400,
                        "sort" => 106,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/1" => [
                        "label" => "作業チェック内容２",
                        "type" => 0,
                        "sort" => 107,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'checklist',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/1/items/0" => [
                        "label" => "対応完了日",
                        "type" => 700,
                        "sort" => 108,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/1',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/1/items/1" => [
                        "label" =>  "対応開始日",
                        "type" => 700,
                        "sort" => 109,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/1',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/1/items/2" => [
                        "label" =>  "メニュー数",
                        "type" => 100,
                        "sort" => 110,
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/1',
                        "layout_option" => null,
                        "validate_option" => '{"format_pattern":"L14oWzEtOV1bMC05XSopezEsMTB9JC8="}' // /^([1-9][0-9]*){1,10}$/
                    ],
                ],
            ],
        ];

        // common_mail
        $common_mail = [
            'mail_to' => 27,
            'cc' => 27,
            'subject' => 7,
            'body' => 7,
            'mail_template' => 7,
            'sign_template' => 7,
            'file_attachment' => 3,
            'check_list_button' => 3,
            'review' => 3,
            'use_time' => 3,
            'unknown' => 3,
            'save_button' => 3,
            $steps[0]['id'] => [
                'condition_cd' => 1,
                'title' => "",
                'content' => "<p>○○ 様</p><p><br></p><p>お疲れ様です。</p><p>オペレーション担当です。</p><p><br></p><p>掲題の件につきまして、出稿調査が完了致しましたので、</p><p>BOXよりご確認をお願い致します。</p><p><br></p><p>▼BOX</p><p>----------------------------------------------------------------------</p><p>リンク先URL：</p><p>有効期限 :</p><p>---------------------------------------------------------------------</p><p><br></p><p>▼ご連絡事項</p><p>----------------------------------------------------------------------</p><p><br></p><p>----------------------------------------------------------------------</p><p><br></p><p>修正・追加事項がございましたら</p><p>ご指摘いただけますと幸いでございます。</p><p><br></p><p>以上、何卒宜しくお願い致します。</p><p><br></p><p>#{signature}</p>",
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

                if (DB::table('allocation_configs')->where('step_id', $step['id'])->doesntExist()) {
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

                if (DB::table('approval_configs')->where('step_id', $step['id'])->doesntExist()) {
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

            // ==============================
            // common mail settings
            // ==============================
            \DB::table('common_mail_settings')->where('business_id', $business['id'])->update(
                ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
            );
            \DB::table('common_mail_settings')->insert(
                [
                    'business_id' => $business['id'],
                    'mail_to' => $common_mail['mail_to'],
                    'cc' => $common_mail['cc'],
                    'subject' => $common_mail['subject'],
                    'body' => $common_mail['body'],
                    'mail_template' => $common_mail['mail_template'],
                    'sign_template' => $common_mail['sign_template'],
                    'file_attachment' => $common_mail['file_attachment'],
                    'check_list_button' => $common_mail['check_list_button'],
                    'review' => $common_mail['review'],
                    'use_time' => $common_mail['use_time'],
                    'unknown' => $common_mail['unknown'],
                    'save_button' => $common_mail['save_button'],
                    'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                ] + $created_columns + $updated_columns
            );

            foreach ($steps as $key => $step) {
                \DB::table('common_mail_body_templates')->where('step_id', $step['id'])->update(
                    ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
                );
                \DB::table('common_mail_body_templates')->insert(
                    [
                        'company_id' => $company_id,
                        'business_id' => $business['id'],
                        'step_id' => $step['id'],
                        'condition_cd' => $common_mail[$step['id']]['condition_cd'],
                        'title' => $common_mail[$step['id']]['title'],
                        'content' => $common_mail[$step['id']]['content'],
                        'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                    ] + $created_columns + $updated_columns
                );

                $checklist_group_ids = \DB::table('common_mail_checklist_groups')
                    ->where('step_id', $step['id'])
                    ->pluck('id');
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
        }
    }
}
