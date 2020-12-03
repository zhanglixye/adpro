<?php
// 経費申請業務用config

return [
    'MAIL_CONFIG' => [
        's00022' => [
            // 邮件在作业实绩JsonFile中的根路径
            'MAIL_CONTENT_MAIN' => ['G00000_27'],
            // 「不明あり」で処理します,担当者へのコメント在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_UNKNOWN' => ['C00200_34'],
        ],
    ],
];
