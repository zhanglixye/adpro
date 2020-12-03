<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00019Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00019Seeder
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
            'id' => 19,
            'name' => 'Xoneチームメール納品業務',
            'description' => '作業完了後、メールにて報告',
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),
        ];

        // step
        $steps = [
            [
                'id' => 25,
                'next_id' => null,
                'name' => 'Xoneチームメール納品業務',
                'type' => \Config::get('const.STEP_TYPE.INPUT'),
                'description' => '作業完了後、メールにて報告',
                'time_unit' => 3,
                'deadline_limit' => 1,
                'end_criteria' => 0,
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),
                'allocation_parallel' => 1,
                'allocation_method' => 2,
                'approval_conditions' => 1,
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),
                'is_auto_approval' => \Config::get('const.FLG.ACTIVE'),
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
                        "sort" => 99,
                        "is_required" => 0,
                        "parent_id" => null,
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0" => [
                        "label" => "作業チェック内容１",
                        "type" => 0,
                        "sort" => 100,
                        "use_screen" => pow(2, \Config::get('const.PROCESS_TYPE.WORK')),
                        "is_required" => 0,
                        "parent_id" => 'checklist',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/0" => [
                        "label" => "宛先（TO,CC）と本文の宛名が合っているか",
                        "type" => 400,
                        "sort" => 101,
                        "values" => [
                            "はい",
                        ],
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/1" => [
                        "label" => "添付ファイルが必要な場合は、添付されているか",
                        "type" => 400,
                        "sort" => 102,
                        "values" => [
                            "はい",
                        ],
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                    "checklist/G00000_27/G00000_33/0/items/2" => [
                        "label" => "納品物をboxへ格納しており、URLを記載している場合リンクに飛べるか",
                        "type" => 400,
                        "sort" => 103,
                        "values" => [
                            "はい",
                        ],
                        "is_required" => 1,
                        "parent_id" => 'checklist/G00000_27/G00000_33/0',
                        "layout_option" => null,
                        "validate_option" => null
                    ],
                ],
            ],
        ];

        // common_mail
        $common_mail = [
            'mail_to' => 31,
            'cc' => 31,
            'subject' => 7,
            'body' => 7,
            'mail_template' => 7,
            'sign_template' => 7,
            'file_attachment' => 7,
            'check_list_button' => 3,
            'review' => 3,
            'use_time' => 3,
            'unknown' => 0,
            'save_button' => 3,
            $steps[0]['id'] => [
                'condition_cd' => 1,
                'title' => "不備・不明なしの場合",
                'content' => "<p>#{signature}</p><p>------------------ Original ------------------</p><p>&gt;From: #{before_mail_from}</p><p>&gt;Date:#{before_mail_date}</p><p>&gt;Subject:#{before_mail_subject}</p><p>&gt;To:#{before_mail_to}</p><p>&gt;CC:#{before_mail_cc}</p><p>#{before_mail_body}</p>",
                'checklist' => [
                    [
                        'content' => '作業チェック内容１',
                        'items' => [
                            ['component_type' => 0, 'content' => '宛先（TO,CC）と本文の宛名が合っているか'],
                            ['component_type' => 0, 'content' => '添付ファイルが必要な場合は、添付されているか'],
                            ['component_type' => 0, 'content' => '納品物をboxへ格納しており、URLを記載している場合リンクに飛べるか'],
                        ]
                    ],
                ]
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

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
        }
    }
}
