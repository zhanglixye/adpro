<?php


namespace App\Services\CommonMail\Replay\Biz\B00021;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CommonMailBodyTemplate;
use App\Models\Queue;
use App\Models\SendMail;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00028Impl extends GenericImpl
{
    /**
     * 获取默认的本文.
     * @param int $task_id 作業ID
     * @return mixed 默认的本文
     */
    public function getDefaultBody(int $task_id)
    {
//        ------------------ Original ------------------
//
//        >From: #{before_mail_from}
//
//        >Date:#{before_mail_date}
//
//        >Subject:#{before_mail_subject}
//
//        >To:#{before_mail_to}
//
//        >CC:#{before_mail_cc}
//        #{before_mail_body}

        // 获取模板
        $template_config_array = \Config::get("biz.b00021.MAIL_CONFIG.s00028.MAIL_REPLAY_BODY_TEMPLATE");
        $template_step_id = $template_config_array['step_id'];
        $template_condition_cd = $template_config_array['condition_cd'];
        $task_ext_info_po = parent::getExtInfoById($task_id, \Auth::user()->id);
        $template = CommonMailBodyTemplate::selectBySelective(
            $task_ext_info_po->company_id,
            $task_ext_info_po->business_id,
            $template_step_id,
            $template_condition_cd
        );
        //邮件
        $email_mixed = parent::getEmailByTaskId($task_id);
        //绑定模板
        $signTemplates = parent::getSignTemplates($task_id, \Auth::user()->id);
        return TemplateEngine::make($template[0]->content, [
            'signature' => $signTemplates === null ? '' : $signTemplates->content,
            'before_mail_from' => $email_mixed->from,
            'before_mail_date' => $email_mixed->recieved_at,
            'before_mail_subject' => $email_mixed->subject,
            'before_mail_to' => $email_mixed->to,
            'before_mail_cc' => $email_mixed->cc,
            'before_mail_body' => parent::makeReplyMailBody($email_mixed->body)
        ]);
    }


    /**
     * 获取ファイル添付.
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed ファイル添付
     */
    public function getDefaultAttachments(int $task_id, int $user_id)
    {
        return [];
    }

    /**
     * 不明あり
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return mixed 実作業内容
     */
    public function doUnknown(int $task_id, int $user_id, array &$content)
    {
//        関係各位
//
//        下記タスクが不備・不明処理となりましたのでかくにんをお願いします。
//
//        ■業務名：#{business_name}(#{business_id})
//        ■作業名：#{task_name}(#{task_id})
//        ■作業者：#{user_name} #{user_mail_address}
//        ■コメント内容：#{comment}

        $task_ext_info_po = $this->getExtInfoById($task_id, $user_id);
        $business_id = $task_ext_info_po->business_id;
        $business_name = $task_ext_info_po->business_name;
        $step_id = $task_ext_info_po->step_id;
        $step_name = $task_ext_info_po->step_name;
        $request_id = $task_ext_info_po->request_id;
        $request_work_id = $task_ext_info_po->request_work_id;

        // 获取担当者
        $task_user = \DB::table('tasks')
            ->selectRaw(
                'users.name user_name,' .
                'users.email user_email'
            )
            ->join('users', 'tasks.user_id', 'users.id')
            ->where('tasks.id', $task_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();
        $task_user_name = $task_user->user_name;
        $task_user_email = $task_user->user_email;

        //「不明あり」で処理します,担当者へのコメント
        $business_id_str = 'b' . str_pad($business_id, 5, '0', STR_PAD_LEFT);
        $step_id_str = 's' . str_pad($step_id, 5, '0', STR_PAD_LEFT);
        $mail_content_main_key = \Config::get("biz.${business_id_str}.MAIL_CONFIG.${step_id_str}.MAIL_CONTENT_MAIN");
        $mail_content_unknown_key = \Config::get("biz.${business_id_str}.MAIL_CONFIG.${step_id_str}.MAIL_CONTENT_UNKNOWN");
        $key_array = array_merge($mail_content_main_key, $mail_content_unknown_key);
        $comment = MailController::arrayIfNull($content, $key_array);

        $comment = str_replace(array("\r\n", "\r", "\n"), "<br>", $comment);

        // 画面URL
        $step_count_result = \DB::table('business_flows')
            ->selectRaw(
                'count(1) cnt'
            )
            ->where('business_flows.business_id', $business_id)
            ->first();
        if ($step_count_result->cnt > 1) {
            $url = \Config::get('app.url') . '/management/request_works/' . $request_work_id;
        } else {
            $url = \Config::get('app.url') . '/management/requests/' . $request_id;
        }

        // 本文生成
        $mail_body = "関係各位<br><br>下記タスクが不備・不明処理となりましたので確認をお願いします。<br><br>■業務名：${business_name}<br>■作業名：${step_name}<br>■作業者：${task_user_name} ${task_user_email}<br>■コメント内容：${comment}<br>";

        $send_mail = new SendMail;
        $send_mail->cc = null;
        $send_mail->request_work_id = $task_ext_info_po->request_work_id;
        $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
        $send_mail->subject = "[${task_id}]：不明ありで処理されました";
        $send_mail->body = $mail_body;
        $send_mail->created_user_id = \Auth::user()->id;
        $send_mail->updated_user_id = \Auth::user()->id;
        $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
        $send_mail->to = $this->getUnknownMailTo($business_id, $task_id, $user_id, $content);
        $send_mail->save();

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => (int)$task_ext_info_po->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = \Auth::user()->id;
        $queue->updated_user_id = \Auth::user()->id;
        $queue->save();
        // 処理キュー登録（send mail）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
        $queue->argument = json_encode(['mail_id' => (int)$send_mail->id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = \Auth::user()->id;
        $queue->updated_user_id = \Auth::user()->id;
        $queue->save();
    }

    /**
     * メール送信者を取得する
     * @param int $task_id 作業ID
     * @return string 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailSendFrom(int $task_id): string
    {
        return 'op-toukatsu@dac.co.jp';
    }
}
