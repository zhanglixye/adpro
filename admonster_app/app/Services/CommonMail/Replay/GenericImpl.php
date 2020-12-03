<?php


namespace App\Services\CommonMail\Replay;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CommonMailCcFrequency;
use App\Models\CommonMailSetting;
use App\Models\CommonMailSignTemplate;
use App\Models\CommonMailBodyTemplate;
use App\Models\Label;
use App\Models\Queue;
use App\Models\RequestMailAttachment;
use App\Models\SendMail;
use App\Services\CommonMail\CommonDownloader;
use App\Models\CommonMailFrequency;
use Illuminate\Support\Facades\DB;

class GenericImpl implements ReplyMailInterface
{
    /**
     * 获取默认的mailSetting.
     * @param int $task_id 作業ID
     * @return mixed 默认的mailSetting
     */
    public function getMailSetting(int $task_id)
    {
        return CommonMailSetting::searchByTaskId($task_id);
    }

    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);
        if ($request_mail_po === null) {
            return '';
        } else {
            $from_str = $request_mail_po->from;
            $to_str = $request_mail_po->to;
            $default_mail_to_str = $from_str . ',' . $to_str;
            $default_mail_to_array = explode(',', $default_mail_to_str);
            $default_mail_to_array_result = array();
            $import_mail_accounts_result = \DB::table('import_mail_accounts')
                ->selectRaw('account')
                ->where('is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                ->get();
            foreach ($default_mail_to_array as $mail_to) {
                $is_sys_account = false;
                foreach ($import_mail_accounts_result as $import_mail_account) {
                    $tmp_mail_account = '<' . $import_mail_account->account . '>';
                    if ($mail_to === $import_mail_account->account || substr_compare($mail_to, $tmp_mail_account, -strlen($tmp_mail_account)) === 0) {
                        $is_sys_account = true;
                        break;
                    }
                }
                if ($is_sys_account === false) {
                    array_push($default_mail_to_array_result, $mail_to);
                }
            }
            return $default_mail_to_array_result;
        }
    }

    /**
     * 获取默认的抄送人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailCc(int $task_id)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);
        if ($request_mail_po === null) {
            return [];
        } else {
            $mail_cc_str = $request_mail_po->cc;
            if (empty($mail_cc_str)) {
                return [];
            } else if (strpos($mail_cc_str, ',')) {
                return explode(',', $mail_cc_str);
            } else {
                return [$mail_cc_str];
            }
        }
    }

    /**
     * 获取默认的标题.
     * @param int $task_id 作業ID
     * @param float $time_zone 客户端的时区
     * @return mixed 默认的标题
     */
    public function getDefaultSubject(int $task_id, float $time_zone = null)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);
        if ($request_mail_po === null) {
            return '';
        } else {
            return 'Re:' . $request_mail_po->subject;
        }
    }

    /**
     * 获取默认的本文.
     * @param int $task_id 作業ID
     * @return mixed 默认的本文
     */
    public function getDefaultBody(int $task_id)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);
        if ($request_mail_po === null) {
            return '';
        } else {
            return $request_mail_po->body;
        }
    }

    /**
     * 获取默认的不明.
     * @param int $task_id 作業ID
     * @return mixed 默认的不明
     */
    public function getDefaultUnknown(int $task_id)
    {
        return null;
    }

    /**
     * 获取默认的作業時間.
     * @param int $task_id 作業ID
     * @return mixed 默认的作業時間
     */
    public function getDefaultUseTime(int $task_id)
    {
        return ['beginDateTime' => null, 'endTime' => null, 'useTimeHour' => 0];
    }

    /**
     * 获取本体テンプレート
     * @param int $task_id 作業ID
     * @return mixed 本体テンプレート
     */
    public function getBodyTemplates(int $task_id)
    {
        $task_ext_info = self::getExtInfoById($task_id, \Auth::user()->id);
        return CommonMailBodyTemplate::selectBySelective(
            $task_ext_info->company_id,
            $task_ext_info->business_id,
            $task_ext_info->step_id,
            null
        );
    }

    /**
     * 获取署名テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed 署名テンプレート
     */
    public function getSignTemplates(int $task_id, int $user_id)
    {
        $task_ext_info = self::getExtInfoById($task_id, \Auth::user()->id);
        return CommonMailSignTemplate::selectBySelective($task_ext_info->business_id, $user_id);
    }

    /**
     * 获取ファイル添付.
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed ファイル添付
     */
    public function getDefaultAttachments(int $task_id, int $user_id)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);

        $fileArray = array();
        $attachments = RequestMailAttachment::where('request_mail_id', $request_mail_po->id)->get();
        // TODO 校验用户是否是Task的担当
        foreach ($attachments as $value) {
            list($src, $mime_type, $file_size) = CommonDownloader::base64FileFromS3($value->file_path, $value->name);
            array_push($fileArray, [
                'file_path' => $value->file_path,
                'file_name' => $value->name,
                'base64_content' => $src,
                'mime_type' => $mime_type,
                'file_size' => $file_size
            ]);
        }
        return $fileArray;
    }

    /**
     * 获取默认的CheckList选项.
     * @param int $task_id 作業ID
     * @return mixed 默认的CheckList选项.
     */
    public function getDefaultChecklistValues(int $task_id)
    {
        return [];
    }


    /**
     * 获取CheckList的项目列表
     * @param int $task_id 作業ID
     * @return mixed 默认的CheckList的项目列表
     */
    public function getChecklistItems(int $task_id, int $user_id)
    {
        $json_array=array();
        $task_ext_info = self::getExtInfoById($task_id, $user_id);
        $item_config_array = self::initJsonFromDb($json_array, $task_ext_info->step_id, null);
        $json_array = MailController::arrayIfNull($json_array[0], ['items']);
        return $json_array;
    }

    /**
     * 获取MailTo頻度
     * @param int $task_id 作業ID
     * @param String $keyword キーワード
     * @return mixed MailTo頻度
     */
    public function searchMailToFrequencyList(int $task_id, String $keyword = null)
    {
        $task_ext_info = self::getExtInfoById($task_id, \Auth::user()->id);
        return CommonMailFrequency::searchMailToFrequencyList($task_ext_info->business_id, $task_ext_info->company_id, $task_ext_info->step_id, $keyword);
    }

    /**
     * 获取MailCc頻度
     * @param int $task_id 作業ID
     * @param String $keyword キーワード
     * @return mixed MailTo頻度
     */
    public function searchMailCcFrequencyList(int $task_id, String $keyword = null)
    {
        $task_ext_info = self::getExtInfoById($task_id, \Auth::user()->id);
        return CommonMailFrequency::searchMailCcFrequencyList($task_ext_info->business_id, $task_ext_info->company_id, $task_ext_info->step_id, $keyword);
    }

    /**
     * 获取default署名テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed default署名テンプレート
     */
    public function getDefaultSignTemplates(int $task_id, int $user_id)
    {
        return null;
    }

    /**
     * 获取本体テンプレート
     * @param int $task_id 作業ID
     * @return mixed 本体テンプレート
     */
    public function getDefaultBodyTemplates(int $task_id)
    {

        return [];
    }

    /**
     * 保存署名テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $data 数据['title' => '','content' => '']
     */
    public function saveSignTemplates(int $task_id, int $user_id, array $data)
    {
        $task_ext_info = self::getExtInfoById($task_id, $user_id);
        $sign_template_po = CommonMailSignTemplate::where('business_id', $task_ext_info->business_id)
            ->where('user_id', $user_id)
            ->where('is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();
        if ($sign_template_po === null) {
            $sign_template_po = new CommonMailSignTemplate();
            $sign_template_po->business_id = $task_ext_info->business_id;
            $sign_template_po->user_id = $user_id;
            $sign_template_po->title = $data['title'];
            $sign_template_po->content = $data['content'];
            $sign_template_po->is_deleted = \Config::get('const.DELETE_FLG.ACTIVE');
            $sign_template_po->created_user_id = $user_id;
            $sign_template_po->updated_user_id = $user_id;
        } else {
            $sign_template_po->title = $data['title'];
            $sign_template_po->content = $data['content'];
        }
        $sign_template_po->save();
    }

    /**
     * 保存本体テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $data 数据[['id' => '','condition_cd' => '', 'title' => '', 'content' => '']]
     */
    public function saveBodyTemplates(int $task_id, int $user_id, array $data)
    {
        $task_ext_info = self::getExtInfoById($task_id, $user_id);
        foreach ($data as $item) {
            $common_mail_body_template = CommonMailBodyTemplate::where('company_id', $task_ext_info->company_id)
                ->where('business_id', $task_ext_info->business_id)
                ->where('step_id', $task_ext_info->step_id)
                ->where('condition_cd', $item['condition_cd'])
                ->where('is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                ->first();
            if ($common_mail_body_template === null) {
                $common_mail_body_template = new CommonMailBodyTemplate();
                $common_mail_body_template->business_id = $task_ext_info->business_id;
                $common_mail_body_template->company_id = $task_ext_info->company_id;
                $common_mail_body_template->step_id = $task_ext_info->step_id;
                $common_mail_body_template->condition_cd = $item['condition_cd'];
                $common_mail_body_template->title = $item['title'];
                $common_mail_body_template->content = $item['content'];
                $common_mail_body_template->is_deleted = \Config::get('const.DELETE_FLG.ACTIVE');
                $common_mail_body_template->created_user_id = $user_id;
                $common_mail_body_template->updated_user_id = $user_id;
            } else {
                $common_mail_body_template->condition_cd = $item['condition_cd'];
                $common_mail_body_template->title = $item['title'];
                $common_mail_body_template->content = $item['content'];
                $common_mail_body_template->created_user_id = $user_id;
                $common_mail_body_template->updated_user_id = $user_id;
            }
            $common_mail_body_template->save();
        }
    }

    /**
     * 通过TaskId和userId取得Task的关联信息
     * @param int $task_id タスクId
     * @param int $current_user_id ユーザーID
     * @return mixed
     */
    protected function getExtInfoById(int $task_id, int $current_user_id)
    {
        $result = \DB::table('businesses')
            ->selectRaw(
                'businesses.id business_id,' .
                'businesses.name business_name,' .
                'businesses.company_id company_id,' .
                'requests.id request_id,' .
                'request_works.id request_work_id,' .
                'request_works.name request_work_name,' .
                'request_works.step_id step_id,' .
                'steps.name step_name,' .
                'tasks.request_work_id request_work_id'
            )->join('requests', 'businesses.id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->where('tasks.id', $task_id)
            ->where('businesses.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('requests.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))->first();
        return $result;
    }

    /**
     * 通过作業Id，获取依頼メール
     * @param int $task_id 作業Id
     * @return mixed
     */
    protected function getEmailByTaskId(int $task_id)
    {
        $query = \DB::table('request_mails')
            ->select(
                'request_mails.id',
                'request_mails.mail_account_id',
                'request_mails.create_status',
                'request_mails.message',
                'request_mails.message_id',
                'request_mails.references',
                'request_mails.in_reply_to',
                'request_mails.reply_to',
                'request_mails.from',
                'request_mails.to',
                'request_mails.cc',
                'request_mails.bcc',
                'request_mails.subject',
                'request_mails.content_type',
                'request_mails.body',
                'request_mails.recieved_at',
                'request_mails.is_deleted',
                'request_mails.created_at',
                'request_mails.created_user_id',
                'request_mails.updated_at',
                'request_mails.updated_user_id'
            )
            ->join('request_work_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->join('tasks', 'request_work_mails.request_work_id', '=', 'tasks.request_work_id')
            ->where('tasks.id', $task_id)
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('request_mails.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();
        return $query;
    }

    /**
     * 判断json返序列化后的数组是不是所有节点的值都为空
     * @param array $data_array json返序列化后的数组
     * @param bool $false_treat_as_empty boolean类型的false是否认为是空
     * @return bool true:全部是空, false:不全部是空
     */
    protected function jsonArrayAllItemEmpty(array $data_array, bool $false_treat_as_empty = false): bool
    {
        foreach ($data_array as $key => $value) {
            if (is_array($value)) {
                $is_empty = self::jsonArrayAllItemEmpty($value, $false_treat_as_empty);
                if (!$is_empty) {
                    return false;
                }
            } else if (is_bool($value) && $false_treat_as_empty) {
                if ($value === true) {
                    return false;
                }
            } else {
                if (!empty($value)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 判断json返序列化后的数组是不是所有节点的值都不为空
     * @param array $data_array json返序列化后的数组
     * @param bool $false_treat_as_empty boolean类型的false是否认为是空
     * @return bool true:全部不是空, false:存在空
     */
    protected function jsonArrayAllItemNotEmpty(array $data_array, bool $false_treat_as_empty = false): bool
    {
        foreach ($data_array as $key => $value) {
            if (is_array($value)) {
                $all_not_empty = self::jsonArrayAllItemNotEmpty($value, $false_treat_as_empty);
                if (!$all_not_empty) {
                    return false;
                }
            } else if (is_bool($value) && $false_treat_as_empty) {
                if ($value === false) {
                    return false;
                }
            } else {
                if (empty($value)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 数组null处理
     * @param array $array 数组
     * @param array $key_array key数组，例（['top','sec'] 对应 ['top' => ['sec' => 'aaaa']])
     * @param mixed|null|array|object $default 如果key不存在时使用的默认值
     * @param bool $empty_check 是否检查空值
     * @return mixed|null|array|object 如果key存在，返回对应的值 否则 返回指定的默认值
     */
    protected function arrayIfNull(array $array, array $key_array, $default = null, bool $empty_check = false)
    {
        if (empty($array)) {
            return $default;
        }
        $value = $array;
        foreach ($key_array as $key) {
            if (empty($value)) {
                return $default;
            }
            if (array_key_exists($key, $value)) {
                $value = $value[$key];
                if ($empty_check) {
                    if (empty($value)) {
                        return $default;
                    }
                }
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * 数组null处理
     * @param array $array 数组
     * @param array $key_array key数组，例（['top','sec'] 对应 ['top' => ['sec' => 'aaaa']])
     * @param string $fail_msg 如果key不存在时抛出的异常信息
     * @param bool $empty_check 是否检查空值
     * @return array|mixed 如果key存在，返回对应的值，否则，抛出异常
     * @throws \Exception key不存在或者值为空
     */
    protected function arrayIfNullFail(array $array, array $key_array, string $fail_msg = 'null point exception', bool $empty_check = false)
    {
        $value = $array;
        if (empty($value)) {
            throw new \Exception($fail_msg);
        }
        foreach ($key_array as $key) {
            if (empty($value)) {
                throw new \Exception($fail_msg);
            }
            if (array_key_exists($key, $value)) {
                $value = $value[$key];
                if ($empty_check) {
                    if (empty($value)) {
                        throw new \Exception($fail_msg);
                    }
                }
                return $value;
            } else {
                throw new \Exception($fail_msg);
            }
        }
    }

    /**
     * 在原邮件内容前面加上>符号
     * @param string $before_mail_body 原邮件内容
     * @return string 带>符号的邮件内容
     */
    protected function makeReplyMailBody(string $before_mail_body): string
    {
        $body_line_arr = explode("\n", $before_mail_body);
        $result_str = '';
        foreach ($body_line_arr as $item) {
            $result_str = $result_str . ' > ' . $item . '<br/>';
        }
        return $result_str;
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
//        ■画面URL：#{url}

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
        $mail_body = "関係各位<br><br>下記タスクが不備・不明処理となりましたので確認をお願いします。<br><br>■業務名：${business_name}(r${request_id})<br>■作業名：${step_name}(r${request_id}-w${request_work_id})<br>■作業者：${task_user_name} ${task_user_email}<br>■コメント内容：${comment}<br>■画面URL：${url}<br>";

        $send_mail = new SendMail;
        $send_mail->cc = null;
        $send_mail->request_work_id = $task_ext_info_po->request_work_id;
        $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
        $send_mail->subject = "【不明あり】${business_name} ${step_name}";
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
     * mail to for 不明あり
     * @param int $business_id BusinessID
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return string mail to
     */
    public function getUnknownMailTo(int $business_id, int $task_id, int $user_id, array &$content)
    {
        //業務の管理者
        $task_ext_info = $this->getExtInfoById($task_id, $user_id);
        $mail_to = '';
        $query= \DB::table('businesses_candidates')
            ->select('businesses_candidates.mail_address')
            ->join('businesses_admin', function ($join) {
                $join->on('businesses_admin.business_id', 'businesses_candidates.business_id')
                    ->on('businesses_admin.user_id', 'businesses_candidates.user_id');
            })
            ->where('businesses_admin.business_id', $business_id);
        $admin_mail_address_collection = $query->get();
        if (!$admin_mail_address_collection->isEmpty()) {
            foreach ($admin_mail_address_collection as $admin_mail_address) {
                if ($mail_to !== '') {
                    $mail_to = $mail_to . ',';
                }
                $mail_to = $mail_to . $admin_mail_address->mail_address;
            }
        }
        return $mail_to;
    }

    /**
     * 复制邮件共通页面以外的其它页面文件的定制方法(在复杂业务场景时,邮件共通的复制其它页面附件的方法无法满足需求时,在业务层面重写该方法)
     * @param array $task_result_content task_result表的content对应的数组表示,直接修改此数组,邮件共通会将结果序列化为json保存到数据库中
     * @param array $task_result_file_array task_result_file的数组,将当前处理页面要保存的task_result_file放入此数组,邮件共通会统一保存到数据库中(邮件共通处理中已经放入了邮件附件文件)
     * @param int $task_id 当前操作的task的task_id
     * @param int $task_result_id 当前操作的ask_result_id
     * @param int $max_seq_no 当前最大的附件文件序号(邮件共通处理中已经处理了部分文件)
     * @return int 本处理结束后,附件文件的序号的最大值
     */
    public function otherPageAttachFile(array &$task_result_content, array &$task_result_file_array, int $task_id, int $task_result_id, int $max_seq_no): int
    {
        return $max_seq_no;
    }

    /**
     * 添付ファイルに許可されているファイルタイプを取得する
     * @param int $task_id 作業ID
     * @return array 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailAttachmentTypeByTaskId(int $task_id): array
    {
        return [];
    }

    /**
     * メール送信者を取得する
     * @param int $task_id 作業ID
     * @return string 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailSendFrom(int $task_id): string
    {
        return sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
    }

    /**
     * 入力の検証
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content_array コンテンツ配列
     * @return array ['result' => 'success|error', 'err_message' => '', 'data' => null];
     */
    public function inputValidation(int $task_id, int $user_id, array $content_array): array
    {
        $content_array = MailController::arrayIfNull($content_array, ['G00000_33']);
        self::inputValidate($content_array, '');
        return MailController::successResult();
    }
    private function inputValidate(array $json_array, String $parent_label = '')
    {
        foreach ($json_array as $item) {
            $item_type = MailController::arrayIfNull($item, ['component_type']);
            $value = MailController::arrayIfNull($item, ['value']);
            $required = MailController::arrayIfNull($item, ['required'], false);
            $label = MailController::arrayIfNull($item, ['content']);
            $validate_option_array = MailController::arrayIfNull($item, ['validate_option']);
            if ($item_type === 310 //combobox item
                || $item_type === 510 //Radio item
                || $item_type === 610 //SelectBox item
                || $item_type === 1000 //link
            ) {
                continue;
            }
            if ($item_type === 0) { //group
                $parent_label_new = '';
                if (!empty($label)) {
                    $parent_label_new = $label;
                    if (!empty($parent_label)) {
                        $parent_label_new = $parent_label_new . '`|`' . $parent_label;
                    }
                }
                $this->inputValidate(MailController::arrayIfNull($item, ['items'], []), $parent_label_new);
            } else {
                if (empty($label)) {
                    //The current item has no title, it's parent title is used.
                    $parent_label_array = (explode('`|`', $parent_label));
                    if (!empty($parent_label_array)) {
                        $label = $parent_label_array[0];
                    }
                }
                if ($required) {
                    $this->requiredValidate($value, $label);
                }
                if (!empty($validate_option_array)) {
                    $this->maxLengthCharValidate($validate_option_array, $value, $label);
                    $this->formatValidate($validate_option_array, $value, $label);
                }
            }
        }
    }
    /**
     * データベースからページの構成アイテムを取得します.
     * @param int $step_id 作業ID
     * @param int|null $parent_item_key 親作業ID(このパラメーターが指定されている場合、親IDに含まれているサブアイテムを照会し、それがnullの場合は最上位アイテムを照会します。)
     * @return array 作業項目設定
     */
    private function getItemConfigFromDb($step_id, $parent_item_key): array
    {
        $sql = ' select item_configs.id, ' .
            '        item_configs.parent_id, ' .
            '        item_configs.step_id, ' .
            '        item_configs.sort, ' .
            '        item_configs.label_id, ' .
            '        item_configs.item_key, ' .
            '        IFNULL (item_configs.item_type,0) as item_type, ' .
            '        item_configs.layout_option, ' .
            '        item_configs.validate_option, ' .
            '        item_configs.is_required, ' .
            '        item_configs.diff_check_level, ' .
            '        item_configs.is_deleted, ' .
            '        item_configs.created_at, ' .
            '        item_configs.created_user_id, ' .
            '        item_configs.updated_at, ' .
            '        item_configs.updated_user_id, ' .
            '        labels.name label_name' .
            ' from item_configs left join labels ' .
            ' on item_configs.label_id = labels.label_id ' .
            ' where item_configs.is_deleted = 0 ' .
            ' and use_screen>> ? & 1';
        if ($parent_item_key === null) {
            $sql = $sql . ' and item_configs.step_id = ?  and item_configs.parent_id is null and item_configs.sort > 0 AND item_configs.item_key = \'checklist\'';
            $params = [\Config::get('const.PROCESS_TYPE.WORK'), $step_id];
        } else {
            $sql = $sql . ' and item_configs.parent_id = ? ';
            $params = [\Config::get('const.PROCESS_TYPE.WORK'), $parent_item_key];
        }
        $sql = $sql . ' order by item_configs.sort ';
        $list = DB::select(
            $sql,
            $params
        );
        return $list;
    }
    /**
     * データベース内のページ構成アイテムを読み取る
     * @param array $json_array ページ構成アイテムを保持する配列への参照
     * @param int $step_id StepId
     * @param int|null $parent_item_key 構成アイテムの親ID(このパラメーターが指定されている場合、親IDに含まれているサブアイテムを照会し、それがnullの場合は最上位アイテムを照会します。)
     */
    private function initJsonFromDb(&$json_array, $step_id, $parent_item_key)
    {
        $item_config_array = self::getItemConfigFromDb($step_id, $parent_item_key);
        if (empty($item_config_array)) {
            return;
        }
        foreach ($item_config_array as $item_config) {
            $layout_option_json_str = $item_config->layout_option;
            $layout_option_json_array = [];
            if (!empty($layout_option_json_str)) {
                $layout_option_json_array = json_decode($layout_option_json_str, true);
            }
            $validate_option_json_str = $item_config->validate_option;
            $validate_option_json_array = [];
            if (!empty($validate_option_json_str)) {
                $validate_option_json_array = json_decode($validate_option_json_str, true);
            }
            $component = [
                'component_type' => $item_config->item_type,
                'content' => $item_config->label_name,
                'required' => $item_config->is_required != 0,
                'order_num' => $item_config->sort,
                'value' => MailController::arrayIfNull($layout_option_json_array, ['value']),
                'validate_option' => $validate_option_json_array,
                'id' => $item_config->id,
            ];
            if ($item_config->item_type === 0 //group
                || $item_config->item_type === 300 //radio group
                || $item_config->item_type === 500 //comboBox group
            ) {
                $component['layout'] = MailController::arrayIfNull($layout_option_json_array, ['layout']);
                $urls = self::makeGroupUrls(MailController::arrayIfNull($layout_option_json_array, ['urls']));
                $component['urls'] = $urls;
                $components = array();
                self::initJsonFromDb($components, $step_id, $item_config->id);
                $component['items'] = $components;
            }
            array_push($json_array, $component);
        }
    }
    private function makeGroupUrls($urls_array)
    {
        if (empty($urls_array)) {
            return null;
        }
        $result_array = [];
        foreach ($urls_array as $url) {
            $label_id = self::arrayIfNull($url, ['label_id']);
            if (!empty($label_id)) {
                $url['label'] = Label::where('label_id', $label_id)->value('name');
            }
            array_push($result_array, $url);
        }
        return $result_array;
    }
    private function requiredValidate($value, $label): void
    {
        if (empty($value)) {
            if ($value === 0 || $value === 0.0 || $value === '0') {
                //0 is not empty
            } else {
                throw new \Exception("${label} の項目の入力やチェックは必須です");
            }
        }
    }
    private function maxLengthCharValidate($validate_option_array, $value, $label): void
    {
        $max_length_char = MailController::arrayIfNull($validate_option_array, ['max_length_char']);
        if ($max_length_char != null) {
            if (!is_array($value) && !is_object($value)) {
                $chars = mb_strlen(strval($value));
                if ($chars > ((int)$max_length_char)) {
                    throw new \Exception("${label}の入力内容は項目桁数定義と一致しません。${max_length_char}文字まで入力可能です");
                }
            }
        }
    }
    private function formatValidate($validate_option_array, $value, $label): void
    {
        $format_pattern = MailController::arrayIfNull($validate_option_array, ['format_pattern']);
        if (!empty($format_pattern)) {
            $format_pattern_decode = base64_decode($format_pattern);
            if (!is_array($value) && !is_object($value) && !self::emptyNotZero($value)) {
                if (!preg_match($format_pattern_decode, strval($value))) {
                    throw new \Exception("${label}の入力内容は項目型定義と一致しません");
                }
            }
        }
    }
    private function emptyNotZero($value)
    {
        if (empty($value)) {
            if ($value === 0 || $value === 0.0 || $value === '0') {
                //0 is not empty
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
