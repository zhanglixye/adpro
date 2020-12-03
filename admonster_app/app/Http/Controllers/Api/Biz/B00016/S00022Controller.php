<?php

namespace App\Http\Controllers\Api\Biz\B00016;

use App\Http\Controllers\Api\Biz\BaseController;
use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Http\Controllers\Api\UtilitiesController;
use App\Models\Business;
use App\Models\Queue;
use App\Models\Request as RequestModel;
use App\Models\RequestMail;
use App\Models\RequestMailAttachment;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\Task;
use App\Models\task_result_file;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\UploadFileManager\Uploader;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;
use stdClass;
use Storage;
use ZipArchive;
use function GuzzleHttp\json_encode;

class S00022Controller extends BaseController
{
    /**
     * XXXXチェック画面.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function create(Request $req)
    {
        try {
            $user = \Auth::user();
            $base_info = parent::create($req)->original;
            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            $mail_attachments = self::getMailAttachments($this->task_id, \Auth::user()->id);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 1没有作业履历，创建新的作业履历
                // 生成默认附件列表
                $default_attachments = self::generateDefaultAttachments($base_info['task_info']->id);
                $default_attachments_seq_no = array();
                foreach ($default_attachments as $attachment) {
                    array_push($default_attachments_seq_no, $attachment['file_seq_no']);
                }
                // 1-1创建新的作业履历到数据库
                $task_result_po = new TaskResult();
                $task_result_po->task_id = $base_info['task_info']->id;
                $task_result_po->step_id = $base_info['request_info']->step->id;
                $task_result_po->created_user_id = \Auth::user()->id;
                $task_result_po->updated_user_id = \Auth::user()->id;
                // 构造content
                $content_array = [
                    'results' =>
                        [
                            'type' => config('const.TASK_RESULT_TYPE.NOT_WORKING'),
                            'comment' => '',
                            'mail_id' => []
                        ],
                    'lastDisplayedPage' => '1',
                    'G00000_1' => [
                    ]
                ];
                $json_encode = json_encode($content_array);
                $task_result_po->content = $json_encode;
                \DB::beginTransaction();
                try {
                    $task_result_po->save();
                    foreach ($default_attachments as $file) {
                        $task_result_file_po = new TaskResultFile();
                        $task_result_file_po->seq_no = $file['file_seq_no'];
                        $task_result_file_po->name = $file['file_name'];
                        $task_result_file_po->file_path = $file['file_path'];
                        $task_result_file_po->size = $file['size'];
                        $task_result_file_po->width = $file['width'];
                        $task_result_file_po->height = $file['height'];
                        $task_result_file_po->task_result_id = $task_result_po->id;
                        $task_result_file_po->created_user_id = \Auth::user()->id;
                        $task_result_file_po->updated_user_id = \Auth::user()->id;
                        $task_result_file_po->save();
                    }
                    \DB::commit();
                } catch (\Throwable $e) {
                    \DB::rollback();
                    throw $e;
                }
                // 1-2 重新构造 task_result_info
                $base_info['task_result_info'] = $task_result_po;
            } else {
                // 1-1反序列化作业履历的content，得到对象
                $task_result_array = json_decode($task_result_info->content, true);
                $task_result_file_array = $task_result_info->taskResultFiles->toArray();
                $content_array = self::contentToView($task_result_array, $task_result_file_array);
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return self::error('初期化失敗しました。');
        }
    }

    /**
     * ファイルをs3にアップロードする.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function uploadFileToS3(Request $req)
    {
        \DB::beginTransaction();
        try {
            $file_name = $req->file_name;
            $file_data = $req->file_data;
            if (empty($file_name) || empty($file_data)) {
                return $this->error('ファイルの形式に不備はあります。');
            }
            // 排他制御
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // 最新の作業履歴
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();

            //ファイル番号を計算する
            $task_result_file_array = $task_result_info->taskResultFiles->toArray();
            $seqNo = 0;
            foreach ($task_result_file_array as $task_result_file) {
                if ($seqNo < (int) $task_result_file['seq_no']) {
                    $seqNo = (int) $task_result_file['seq_no'];
                }
            }
            $seqNo++;
            list(, $fileData) = explode(';', $file_data);
            list(, $fileData) = explode(',', $fileData);
            $file_contents = base64_decode($fileData);
            $file_path = 'task_result_files/B00016/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;

            //ファイルをデータベースに保存する
            $task_result_file_po = Uploader::tryUploadAndSave($file_contents, $file_path, 'App\Models\TaskResultFile', ['seq_no' => $seqNo, 'task_result_id' => $task_result_info->id]);

            //ファイル名変換
            $check_file_name = self::convert($task_result_file_po->name, number_format($task_result_file_po->size)); //チェックファイル名

            \DB::commit();

            return self::success(
                [
                    'seq_no' => $seqNo,
                    'file_name' => $task_result_file_po->name,
                    'file_path' => $task_result_file_po->file_path,
                    'file_size' => $task_result_file_po->size,
                    'display_size' => $task_result_file_po->width . 'x' . $task_result_file_po->height,
                    'check_file_name' => $check_file_name
                ]
            );
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }
    /**
     * 作業内容を保存する.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function saveWork(Request $req)
    {
        try {
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }

            $data = self::fileValidate($data, 1);
            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null, 'remark' => null];
            $work_time['started_at'] = MailController::convertUserTimezoneToUtc0(MailController::arrayIfNull($data, ['G00000_18', 'C00700_19']));
            $work_time['finished_at'] = MailController::convertUserTimezoneToUtc0(MailController::arrayIfNull($data, ['G00000_18', 'C00700_20']));
            $work_time['remark'] = MailController::arrayIfNull($data, ['G00000_18', 'C00700_22']);
            $work_time['total'] = MailController::arrayIfNull($data, ['G00000_18', 'C00700_21']);
            unset($data['G00000_18']);
            $db_task_result_info = self::convertToDb($data);
            self::taskTemporarySave($db_task_result_info['task_result_array'], $db_task_result_info['task_result_file_array'], $work_time);
            return self::success();
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。' . $e->getMessage());
        }
    }
    /**
     * 不明あり.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     * @throws \Throwable 不明あり実行失敗
     */
    public function wrongWork(Request $req)
    {
        try {
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }

            $data = self::fileValidate($data, 1);

            MailController::arrayIfNullFail($data, ['comment'], '[担当者へのコメント]の入力は必須です。', true);

            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null, 'remark' => null];
            $work_time['started_at'] = MailController::convertUserTimezoneToUtc0(MailController::arrayIfNull($data, ['G00000_18', 'C00700_19']));
            $work_time['finished_at'] = MailController::convertUserTimezoneToUtc0(MailController::arrayIfNull($data, ['G00000_18', 'C00700_20']));
            $work_time['remark'] = MailController::arrayIfNull($data, ['G00000_18', 'C00700_22']);
            $work_time['total'] = MailController::arrayIfNull($data, ['G00000_18', 'C00700_21']);
            unset($data['G00000_18']);
            $commnet = $data['comment'];
            $db_task_result_info = self::convertToDb($data);

            self::taskContact($db_task_result_info['task_result_array'], $db_task_result_info['task_result_file_array'], $work_time, $commnet);

            return self::success();
        } catch (\Exception $e) {
            report($e);
            return self::error($e->getMessage());
        }
    }
    /**
     * ユーザーが送信したファイルの有効性を確認します.
     * @param array $view_content_array ビュー形式のJSONファイル
     * @param int $flg 判断验证类型 1:保存、2:处理
     * @return array ファイル情報を処理した後のJSONファイル
     * @throws Exception 不正なファイルが存在します
     */
    private function fileValidate(array $view_content_array, int $flg): array
    {
        // 最新の作業履歴
        $task_result_info = TaskResult::with('taskResultFiles')
            ->where('task_id', $this->task_id)
            ->orderBy('id', 'desc')
            ->first();

        //タスク実績（ファイル）
        $task_result_file_array = $task_result_info == null ? [] : $task_result_info->taskResultFiles->toArray();
        $file_map = array();
        foreach ($task_result_file_array as $task_result_file) {
            $file_map[$task_result_file['seq_no']] = $task_result_file;
        }
        // file validate
        $task_result_AD = MailController::arrayIfNull($view_content_array, ['G00000_3'], []);
        if ($flg == "2") {
            self::validationData($task_result_AD, $view_content_array['C00400_2']);
        }

        for ($i = 0; $i < count($task_result_AD); $i++) {
            $row = $i + 1;
//            //AD 传数组
            $ADList = MailController::arrayIfNull($view_content_array, ['G00000_3'], []);
            // 画像素材ファイル
            $AD_seqNo = MailController::arrayIfNull($ADList[$i], ['C00800_6'], [], true);

            for ($j = 0; $j < count($AD_seqNo); $j++) {
                $seqNo = MailController::arrayIfNull($AD_seqNo[$j], ['seq_no'], -1, true);
                if ($seqNo>0) {
                    $task_result_file = MailController::arrayIfNullFail($file_map, [$seqNo], "[ ${row} 番目の画像素材ファイル]は存在しません。", true);
                    // ignore client value
                    $view_content_array['G00000_3'][$i]['C00800_6'][$j]['file_name'] = $task_result_file['name'];
                    $view_content_array['G00000_3'][$i]['C00800_6'][$j]['file_path'] = $task_result_file['file_path'];
                } else {
                    $view_content_array['G00000_3'][$i]['C00800_6'][$j]['file_name'] = null;
                    $view_content_array['G00000_3'][$i]['C00800_6'][$j]['file_path'] = null;
                }
            }
//            // 画像素材ファイル
//            $seqNo = MailController::arrayIfNull($task_result_AD[$i], ['C00800_6','seq_no'], -1, true);
//            // exist check
//            if ($seqNo>0) {
//                $task_result_file = MailController::arrayIfNullFail($file_map, [$seqNo], "[ ${row} 番目の画像素材ファイル]は存在しません。", true);
//                // ignore client value
//                $view_content_array['G00000_3'][$i]['C00800_6']['file_name'] = $task_result_file['name'];
//                $view_content_array['G00000_3'][$i]['C00800_6']['file_path'] = $task_result_file['file_path'];
//            } else {
//                $view_content_array['G00000_3'][$i]['C00800_6']['file_name'] = null;
//                $view_content_array['G00000_3'][$i]['C00800_6']['file_path'] = null;
//            }
            // file validate
            $LPList = MailController::arrayIfNull($view_content_array, ['G00000_3'], []);
            // 結果キャプチャーファイル
            $LP_seqNo = MailController::arrayIfNull($LPList[$i], ['G00800_7'], [], true);
            for ($j = 0; $j < count($LP_seqNo); $j++) {
                $seqNo = MailController::arrayIfNull($LP_seqNo[$j], ['seq_no'], -1, true);
                if ($seqNo < 0) {
                    // ignore client value
                    $view_content_array['G00000_3'][$i]['G00800_7'][$j]['seq_no'] = null;
                    $view_content_array['G00000_3'][$i]['G00800_7'][$j]['file_name'] = null;
                    $view_content_array['G00000_3'][$i]['G00800_7'][$j]['file_path'] = null;
                } else {
                    // exist check
                    $task_result_file = MailController::arrayIfNullFail($file_map, [$seqNo], "[ 番目の結果キャプチャーファイル]は存在しません。", true);
                    // ignore client value
                    $view_content_array['G00000_3'][$i]['G00800_7'][$j]['file_name'] = $task_result_file['name'];
                    $view_content_array['G00000_3'][$i]['G00800_7'][$j]['file_path'] = $task_result_file['file_path'];
                }
            }
        }
        return $view_content_array;
    }
    /**
     * 作業内容を保存する.
     * @param array $content 作業内容
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $work_time 作業時間
     * @throws Exception 保存に失敗しました
     */
    private function taskTemporarySave(array $content, array $task_result_file_array, array $work_time = null)
    {
        $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.HOLD');
        \DB::beginTransaction();
        try {
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // タスクのステータスを対応中に更新
            $task = Task::findOrFail($this->task_id);
            $task->status = config('const.TASK_STATUS.ON');
            $task->updated_user_id = \Auth::user()->id;
            $task->save();

            // タスク実績
            $task_result = new TaskResult;
            $task_result->task_id = $this->task_id;
            $task_result->step_id = $this->step_id;
            // 作業時間は手入力値
            // TODO: 手入力する枠が画面にない場合
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null, 'remark' => null];
            }
            $task_result->started_at = $work_time['started_at'];
            $task_result->finished_at = $work_time['finished_at'];
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $task_result->work_time = $work_time['total'];
            $task_result->work_time_comment = $work_time['remark'];
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();
            // タスク実績（ファイル）
            foreach ($task_result_file_array as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->task_result_id = $task_result->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }
    /**
     * リクエストメールの添付ファイル情報の取得.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @return array リクエストメールの添付ファイル情報
     * @throws Exception 添付ファイルの取得に失敗しました
     */
    private function getMailAttachments(int $task_id, int $user_id) : array
    {
        $task = self::exclusiveTaskByUserId($task_id, $user_id, false);
        $request_work = RequestWork::findOrFail($task->request_work_id);
        $mail_id = $request_work->requestMails[0]->id;
        // 添付ファイルの取得
        $select_column = ['id', 'name', 'file_path', 'size', 'width', 'height'];
        $mail_attachments = RequestMail::find($mail_id)->requestMailAttachments()->select($select_column)->get();
        $attachment_files = []; //依頼メールの添付
        $attachment_extra_files = []; //依頼メールの添付（追加）
        $attachment_unzipped_files = []; //依頼メールの添付（解凍済み）
        $attachment_not_unzipped_files = []; //依頼メールの添付（解凍されていません）
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        foreach ($mail_attachments as $attachment) {
            $attachment_info = new stdClass();
            $attachment_info->attachment_id = $attachment->id; //ID
            $attachment_info->id = $attachment->id; //ID
            $attachment_info->name = $attachment->name; //ファイル名
            $attachment_info->file_path = $attachment->file_path; //ファイルパス
            $attachment_info->width = $attachment->width; //幅(px)
            $attachment_info->height = $attachment->height; //高さ(px)
            $attachment_info->file_size = $disk->size($attachment->file_path); //ファイルサイズ TODO 課題ADTAKT_PF-4
            $extension = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
            if ($extension === 'zip') {
                array_push($attachment_not_unzipped_files, $attachment_info);
            }
            array_push($attachment_files, $attachment_info);
        }
        // 依頼メールの添付（追加）から取得
        $size = count($attachment_not_unzipped_files);
        for ($i = 0; $i < $size; $i++) {
            //依頼メールの添付（解凍されていません）キューの最初の要素をポップします
            $zip_file = array_shift($attachment_not_unzipped_files);
            //依頼メールの添付（追加）から取得
            $mail_attachment_extras = \DB::table('request_mail_attachment_extra')
                ->select('*')
                ->where('mail_attachment_id', $zip_file->id)
                ->get();
            if (empty($mail_attachment_extras->all())) {
                //依頼メールの添付（追加）が存在しません,依頼メールの添付（解凍されていません）の最後までプッシュする
                array_push($attachment_not_unzipped_files, $zip_file);
            } else {
                //依頼メールの添付（追加）が存在します,依頼メールの添付（解凍済み）キューに追加
                array_push($attachment_unzipped_files, $zip_file);
                //依頼メールの添付（追加）
                foreach ($mail_attachment_extras as $attachment_extra) {
                    $attachment_info = new stdClass();
                    $attachment_info->attachment_id = $zip_file->id; //ID
                    $attachment_info->id = $attachment_extra->id; //ID
                    $attachment_info->name = $attachment_extra->name; //ファイル名
                    $attachment_info->file_path = $attachment_extra->file_path; //ファイルパス
                    $attachment_info->width = $attachment_extra->width; //幅(px)
                    $attachment_info->height = $attachment_extra->height; //高さ(px)
                    $attachment_info->file_size = $disk->size($attachment_extra->file_path); //ファイルサイズ TODO 課題ADTAKT_PF-4
                    array_push($attachment_extra_files, $attachment_info);
                }
            }
        }
        return [
            'attachment_files' => $attachment_files, //依頼メールの添付
            'attachment_extra_files' => $attachment_extra_files, //依頼メールの添付（追加）
            'attachment_unzipped_files' => $attachment_unzipped_files, //依頼メールの添付（解凍済み）
            'attachment_not_unzipped_files' => $attachment_not_unzipped_files //依頼メールの添付（解凍されていません）
        ];
    }
    /**
     * 排他制御.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @throws \Exception 排他失败
     * @return mixed
     */
    private function exclusiveTaskByUserId(int $task_id, int $user_id, bool $lock = true)
    {
        $ext_info_mixed = self::getExtInfoByTeskId($task_id);
        $is_business_admin = self::isBusinessAdmin($ext_info_mixed->business_id, $user_id);
        $query = Task::where('id', $task_id);
        if (!$is_business_admin) {
            $query = $query->where('user_id', $user_id);
        }
        if ($lock) {
            $query = $query->lockForUpdate();
        }
        $task = $query->first();
        if ($task === null) {
            throw new \Exception("The task does not exist or does not belong to you, task_id:$task_id user_id:$user_id");
        }
        return $task;
    }
    /**
     * 不明あり.
     * @param array $content 作業内容
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $work_time 作業時間
     * @param String $comment 不明原因
     * @throws \Throwable 保存に失敗しました
     */
    private function taskContact(array $content, array $task_result_file_array, array $work_time = null, String $comment)
    {
        $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.CONTACT');
        \DB::beginTransaction();
        try {
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // タスクのステータスを完了に更新
            $task = Task::findOrFail($this->task_id);
            $task->status = config('const.TASK_STATUS.DONE');
            //ADPORTER_PF-252 各作業画面) 不明点あり時の処理追加 不備・不明（1:あり）
            $task->is_defective = config('const.FLG.ACTIVE');
            $task->updated_user_id = \Auth::user()->id;
            $task->save();

            // タスク実績
            $task_result = new TaskResult;
            $task_result->task_id = $this->task_id;
            $task_result->step_id = $this->step_id;
            // 作業時間は手入力値
            // TODO: 手入力する枠が画面にない場合
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null, 'remark' => null];
            }
            $task_result->started_at = $work_time['started_at'];
            $task_result->finished_at = $work_time['finished_at'];
            $task_result->work_time = $work_time['total'];
            $task_result->work_time_comment = $work_time['remark'];
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績（ファイル）
            foreach ($task_result_file_array as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path =  $task_result_file['file_path'];
                $task_result_file_po->task_result_id = $task_result->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }
            // メールの登録
            $content['G00000_27'] = ['C00200_34' => $comment];
            MailController::getMailImplementsByPrefix($this->task_id)->doUnknown($this->task_id, \Auth::user()->id, $content);

            // ログ登録
            $request_log_attributes = [
                'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT'),
                'request_id' => $this->request_id,
                'request_work_id' => $this->request_work_id,
                'task_id' => $this->task_id,
                'created_user_id' => \Auth::user()->id,
                'updated_user_id' => \Auth::user()->id,
            ];
            $this->storeRequestLog($request_log_attributes);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }
    /**
     * TaskIdでタスク関連情報を取得する.
     * @param int $task_id タスクId
     * @return mixed
     */
    protected function getExtInfoByTeskId(int $task_id)
    {
        $result = \DB::table('businesses')
            ->selectRaw(
                'businesses.id business_id,' .
                'businesses.company_id,' .
                'request_works.step_id,' .
                'tasks.request_work_id'
            )->join('requests', 'businesses.id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->where('tasks.id', $task_id)
            ->where('businesses.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('requests.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
//            ->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'))
//            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))
            ->first();
        return $result;
    }

    /**
     * 処理する.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     * @throws \Throwable 処理に失敗しました
     */
    public function commitWork(Request $req)
    {
        try {
            // $user = \Auth::user();
            // $base_info = parent::create($req)->original;
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }
            $data = self::fileValidate($data, 2);
            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null, 'remark' => null];
            $work_time['started_at'] = MailController::convertUserTimezoneToUtc0(MailController::arrayIfNull($data, ['G00000_18', 'C00700_19']));
            $work_time['finished_at'] = MailController::convertUserTimezoneToUtc0(MailController::arrayIfNull($data, ['G00000_18', 'C00700_20']));
            $work_time['remark'] = MailController::arrayIfNull($data, ['G00000_18', 'C00700_22']);
            $work_time['total'] = MailController::arrayIfNull($data, ['G00000_18', 'C00700_21']);
            unset($data['G00000_18']);
            //JSONファイルからデータベース形式変換する
            $db_task_result_info = self::convertToDb($data);
            //作業内容を保存する
            self::taskSave($db_task_result_info['task_result_array'], $db_task_result_info['task_result_file_array'], $work_time);

            return self::success();
        } catch (\Exception $e) {
            report($e);
            return self::error($e->getMessage());
        }
    }

    /**
     * 現在のユーザーが業務の管理者であるかどうかを確認します.
     * @param int $business_id 業務Id
     * @param int $user_id ユーザーId
     * @return bool true or false
     */
    private function isBusinessAdmin(int $business_id, int $user_id): bool
    {
        $result = \DB::table('businesses_admin')
            ->where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->count();
        return $result > 0;
    }

    /**
     * 特定のファイルのチェック.
     * @param string $file_name ファイル名
     * @return bool true:有効な,false:無効
     */
    private function specificExtensionCheck(string $file_name): bool
    {
        // Exclusion hidden file name
        if (mb_substr($file_name, 0, 1) == '.') {
            return false;
        }
        $tmp_list = explode('.', $file_name);
        $extension = array_pop($tmp_list);
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'mp4':
                return true;
            default:
                return false;
        }
    }

    /**
     * JSONファイルからビュー形式変換する.
     * @param array $db_content_array DB形式のJSONファイル
     * @param array $task_result_file_array 結果ファイル
     * @return array ビュー形式のJSONファイル
     * @throws Exception 変換に失敗しました
     */
    private function contentToView(array $db_content_array, array $task_result_file_array): array
    {
        $view_content_array = [];
        $view_content_array['results'] = MailController::arrayIfNull($db_content_array, ['results'], \Config::get('biz.b00007.DEFAULT_CONTENT_RESULTS'));
        // 最後に表示していたページ
        $view_content_array['lastDisplayedPage'] = MailController::arrayIfNull($db_content_array, ['lastDisplayedPage'], '0');
        $view_content_array['results']['mail_id'] = [];
        $view_content_array['results']['comment'] = "";
        // ファイルサイズを取得
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        // AbbeyCheck処理画面データ
        $G00000_1_array = MailController::arrayIfNull($db_content_array, ['G00000_1'], []);
        $item_array = array();
        $G00000_3 = array();
        for ($i = 0; $i < count($G00000_1_array); $i++) {
            $item_array['C00400_2'] = $G00000_1_array[0]['C00400_2'];
            //array_push($item_array, $item_array['C00400_2']);
            $AD_array = [];

            //AD画像素材 传数组
            $ad_has_result_capture = false;
            $C00800_18_seq_no = MailController::arrayIfNull($G00000_1_array[$i], ['C00800_18']);
            if (count($C00800_18_seq_no) > 0 || $C00800_18_seq_no ==null) {
                $AD_array['C00800_6'] = array();
                $ADitem['C00800_6'] = array();
                if ($C00800_18_seq_no == null) {
                    $ADitem['C00800_6']['seq_no'] = null;
                    $ADitem['C00800_6']['file_name'] = null; //ファイル名
                    $ADitem['C00800_6']['file_path'] = null; //ファイルパス
                    $file_size = null; //ファイルサイズを取得
                    $ADitem['C00800_6']['file_size'] = 0; //ファイルサイズ  TODO 課題ADTAKT_PF-4
                    $ADitem['C00800_6']['ADThumbnail'] = "";
                    $ADitem['C00800_6']['check_file_name'] = null;
                    $ADitem['C00800_6']['display_size'] = null;
                    array_push($AD_array['C00800_6'], $ADitem['C00800_6']);
                    $ad_has_result_capture = false;
                } else {
                    for ($j = 0; $j < count($C00800_18_seq_no); $j++) {
                        foreach ($task_result_file_array as $task_result_file) {
                            if ($C00800_18_seq_no[$j] == null) {
                                $ADitem['C00800_6']['seq_no'] = null;
                                $ADitem['C00800_6']['file_name'] = null; //ファイル名
                                $ADitem['C00800_6']['file_path'] = null; //ファイルパス
                                $file_size = null; //ファイルサイズを取得
                                $ADitem['C00800_6']['file_size'] = 0; //ファイルサイズ  TODO 課題ADTAKT_PF-4
                                $ADitem['C00800_6']['ADThumbnail'] = "";
                                $ADitem['C00800_6']['check_file_name'] = null;
                                $ADitem['C00800_6']['display_size'] = null;
                                array_push($AD_array['G00800_6'], $ADitem['C00800_6']);
                                $ad_has_result_capture = true;
                            }
                            if ($task_result_file['seq_no'] == $C00800_18_seq_no[$j]) {
                                $AdArray['C00800_6']['seq_no'] = $C00800_18_seq_no[$j];
                                $AdArray['C00800_6']['file_name'] = $task_result_file_array[$j]['name']; //ファイル名
                                $AdArray['C00800_6']['file_path'] = $task_result_file_array[$j]['file_path']; //ファイルパス
                                $file_size = $disk->size($task_result_file_array[$j]['file_path']); //ファイルサイズを取得
                                $AdArray['C00800_6']['file_size'] = $file_size; //ファイルサイズ  TODO 課題ADTAKT_PF-4
                                $AdArray['C00800_6']['ADThumbnail'] = "";
                                $AdArray['C00800_6']['check_file_name'] = self::convert($task_result_file['name'], number_format($file_size));
                                $AdArray['C00800_6']['display_size'] = $disk->size($task_result_file_array[$j]['file_path']);
                                array_push($AD_array['C00800_6'], $AdArray['C00800_6']);
                                $ad_has_result_capture = true;
                            }
                        }
                    }
                }


            //AD画像素材
            //$C00800_18_seq_no = MailController::arrayIfNull($G00000_1_array[$i], ['C00800_18'], -1);
                $AD_array['C00700_4'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00700_4'], -1);
                $AD_array['C00100_5'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00100_5'], -1);
                $AD_array['C00200_8'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00200_8'], -1);
            //ファイル情報検索
            //if ($C00800_18_seq_no > 0 || $C00800_18_seq_no ==null) {
//                if ($C00800_18_seq_no ==null) {
//                    $AD_array['C00800_6']['seq_no'] = null;
//                    $AD_array['C00800_6']['file_name'] = null; //ファイル名
//                    $AD_array['C00800_6']['file_path'] = null; //ファイルパス
//                    $file_size = null; //ファイルサイズを取得
//                    $AD_array['C00800_6']['file_size'] = 0; //ファイルサイズ  TODO 課題ADTAKT_PF-4
//                    $AD_array['C00800_6']['ADThumbnail'] = "";
//                    $AD_array['C00800_6']['check_file_name'] = null;
//                    $AD_array['C00800_6']['display_size'] = null;
//                }
//                foreach ($task_result_file_array as $task_result_file) {
//                    if ($task_result_file['seq_no'] == $C00800_18_seq_no) {
//                        $AD_array['C00800_6']['seq_no'] = $C00800_18_seq_no;
//                        $AD_array['C00800_6']['file_name'] = $task_result_file_array[$i]['name']; //ファイル名
//                        $AD_array['C00800_6']['file_path'] = $task_result_file_array[$i]['file_path']; //ファイルパス
//                        $file_size = $disk->size($task_result_file_array[$i]['file_path']); //ファイルサイズを取得
//                        $AD_array['C00800_6']['file_size'] = $file_size; //ファイルサイズ  TODO 課題ADTAKT_PF-4
//                        $AD_array['C00800_6']['ADThumbnail'] = "";
//                        $AD_array['C00800_6']['check_file_name'] = self::convert($task_result_file['name'], number_format($file_size));
//                        $AD_array['C00800_6']['display_size'] = $disk->size($task_result_file_array[$i]['file_path']);
//                    }
//                }

                //LP画像素材
                $C00800_19_seq_no = MailController::arrayIfNull($G00000_1_array[$i], ['C00800_19']);
                $has_result_capture = false;
                if ($C00800_19_seq_no != null) {
                    $AD_array['G00800_7'] = array();
                    $LPitem['C00800_7'] = array();
                    for ($j = 0; $j < count($C00800_19_seq_no); $j++) {
                        if ($C00800_19_seq_no[$j] == null) {
                            $LPitem['C00800_7']['seq_no'] = null;
                            $LPitem['C00800_7']['file_name'] = ""; //ファイル名
                            $LPitem['C00800_7']['file_path'] = ""; //ファイルパス
                            $LPitem['C00800_7']['file_size'] = null; //ファイルサイズを取得 TODO 課題ADTAKT_PF-4
                            $LPitem['C00800_7']['thumbnail'] = "";
                            $LPitem['C00800_7']['materialData'] = "";
                            $LPitem['C00800_7']['itemCheck'] = false;
                            $adposition = MailController::arrayIfNull($G00000_1_array[$i], ['C00200_20']);
                            if ($adposition!=""&&$adposition!=null) {
                                $LPitem['C00800_7']['adPosition'] = $adposition[$j];
                            }
                            array_push($AD_array['G00800_7'], $LPitem['C00800_7']);
                            $has_result_capture = true;
                        }
                        //ファイル情報検索
                        foreach ($task_result_file_array as $task_result_file) {
                            if ($task_result_file['seq_no'] == $C00800_19_seq_no[$j]) {
                                $LPitem['C00800_7']['seq_no'] = $C00800_19_seq_no[$j];
                                $LPitem['C00800_7']['file_name'] = $task_result_file['name']; //ファイル名
                                $LPitem['C00800_7']['file_path'] = $task_result_file['file_path']; //ファイルパス
                                $LPitem['C00800_7']['file_size'] = $disk->size($task_result_file['file_path']); //ファイルサイズを取得 TODO 課題ADTAKT_PF-4
                                $LPitem['C00800_7']['thumbnail'] = "";
                                $LPitem['C00800_7']['materialData'] = "";
                                $LPitem['C00800_7']['itemCheck'] = false;
                                $adposition = MailController::arrayIfNull($G00000_1_array[$i], ['C00200_20']);
                                $LPitem['C00800_7']['adPosition'] = $adposition[$j];
                                array_push($AD_array['G00800_7'], $LPitem['C00800_7']);
                                $has_result_capture = true;
                            }
                        }
                    }
                }
                if (!$has_result_capture) {
                    // 結果キャプチャーが存在しません
                    $AD_array['G00800_7'] = [];
                }
                if (!$ad_has_result_capture) {
                    // 結果キャプチャーが存在しません
                    $AD_array['C00800_6'] = [];
                }
                //多选框赋值
                $AD_array['C00400_9'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_9']);
                $AD_array['C00400_10'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_10']);
                $AD_array['C00400_11'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_11']);
                $AD_array['C00400_12'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_12']);
                $AD_array['C00400_13'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_13']);
                $AD_array['C00400_14'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_14']);
                $AD_array['C00400_15'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_15']);
                $AD_array['C00400_16'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_16']);
                $AD_array['C00400_17'] = MailController::arrayIfNull($G00000_1_array[$i], ['C00400_17']);

//            $file_item['adPosition'] = MailController::arrayIfNullFail($item, ['adPosition']);
//            $file_item['itemCheck'] = MailController::arrayIfNullFail($item, ['itemCheck']);
//            $file_item['materialData'] = MailController::arrayIfNullFail($item, ['materialData']);
//            $file_item['thumbnail'] = MailController::arrayIfNullFail($item, ['thumbnail']);
                array_push($G00000_3, $AD_array);
            }
        }
        $item_array['G00000_3'] = $G00000_3;
        //画像素材
        $view_content_array['data'] = $item_array;
//        //クライアント名
//        $view_content_array['C00400_2'] = MailController::arrayIfNull($db_content_array, ['C00400_2']);
        //「不明あり」で処理します,担当者へのコメント
        //$view_content_array['unknown_comment'] = $view_content_array['results']['comment'];
        return $view_content_array;
    }

    /**
     * ファイル名変換.
     * @param string $file_name ファイル名
     * @param string $file_size ファイルサイズ
     * @return string 変換されたファイル名
     */
    private function convert(string $file_name, string $file_size): string
    {
        return self::shortHash($file_name, $file_size) . substr($file_name, strrpos($file_name, '.'));
    }

    /**
     * 引数で受け取った文字列の配列の値をつなげ、crc32->16進数でハッシュ化.
     * @param mixed ...$arg 文字列
     * @return string crc32-> 16進文字列として表されます
     */
    private function shortHash(...$arg): string
    {
        $data = '';
        foreach ($arg as $key => $value) {
            $data = $data . $value;
        }

        return dechex(crc32($data));
    }

    /**
     * 作業内容を初期化する.
     * @param int $task_id タスクID
     * @param int $step_id ステップID
     * @param array $view_content_array ビュー形式のJSONファイル
     * @return TaskResult タスク結果
     * @throws Exception 初期化に失敗しました
     */
    private function saveContent(int $task_id, int $step_id, array $view_content_array)
    {
        \DB::beginTransaction();
        try {
            // 排他制御
            $this->exclusiveTask($this->task_id, \Auth::user()->id);
            $db_task_result_info = self::convertToDb($view_content_array);
            // データベースに新しいジョブ履歴書を作成する
            $task_result_po = new TaskResult();
            $task_result_po->task_id = $task_id;
            $task_result_po->step_id = $step_id;
            $task_result_po->created_user_id = \Auth::user()->id;
            $task_result_po->updated_user_id = \Auth::user()->id;
            $task_result_po->content = json_encode($db_task_result_info['task_result_array']);
            $task_result_po->save();

            // 新しく生成されたファイルをデータベースに挿入します
            foreach ($db_task_result_info['task_result_file_array'] as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->width = MailController::arrayIfNull($task_result_file, ['width']);
                $task_result_file_po->height = MailController::arrayIfNull($task_result_file, ['height']);
                $task_result_file_po->size = MailController::arrayIfNull($task_result_file, ['size']);
                $task_result_file_po->task_result_id = $task_result_po->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }
            \DB::commit();
            return $task_result_po;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * JSONファイルからデータベース形式変換する.
     * @param array $view_content_array ビュー形式のJSONファイル
     * @return array ビュー形式のJSONファイル
     * @throws Exception 変換に失敗しました
     */
    private function convertToDb(array $view_content_array): array
    {
        $db_content_array = [];
        $db_content_array['results'] = MailController::arrayIfNull($view_content_array, ['results'], \Config::get('biz.b00016.DEFAULT_CONTENT_RESULTS'));
        $db_content_array['G00000_1'] = [];
        //「不明あり」で処理します,担当者へのコメント
        //$db_content_array['results']['comment'] = self::arrayIfNull($view_content_array, ['unknown_comment']);
        $db_content_array['results']['comment'] = MailController::arrayIfNull($view_content_array, ['comment'], []);
        // 最後に表示していたページ
        $db_content_array['lastDisplayedPage'] = '0';
        $db_content_array['results']['mail_id'] = [];
        $item_array_AD = MailController::arrayIfNull($view_content_array, ['G00000_3'], []);
        //存储图片素材信息
        $task_result_file_array = [];
        $G00000_1 = array();
        $G00000_1['C00400_2'] = MailController::arrayIfNull($view_content_array, ['C00400_2']);
        foreach ($item_array_AD as $item) {
            //$seqNo = MailController::arrayIfNull($item, ['C00800_6', 'seq_no'], -1, true);

            //存储 多选框选中状态
            $G00000_1['C00400_9'] = MailController::arrayIfNull($item, ['C00400_9']);
            $G00000_1['C00400_10'] = MailController::arrayIfNull($item, ['C00400_10']);
            $G00000_1['C00400_11'] = MailController::arrayIfNull($item, ['C00400_11']);
            $G00000_1['C00400_12'] = MailController::arrayIfNull($item, ['C00400_12']);
            $G00000_1['C00400_13'] = MailController::arrayIfNull($item, ['C00400_13']);
            $G00000_1['C00400_14'] = MailController::arrayIfNull($item, ['C00400_14']);
            $G00000_1['C00400_15'] = MailController::arrayIfNull($item, ['C00400_15']);
            $G00000_1['C00400_16'] = MailController::arrayIfNull($item, ['C00400_16']);
            $G00000_1['C00400_17'] = MailController::arrayIfNull($item, ['C00400_17']);
//            //AD传数组
//            //ADUrl 赋值 start
//            //获取AD 所有上传素材
            $ADList = MailController::arrayIfNull($item, ['C00800_6'], []);
            $G00000_1['C00800_18'] = array();
            $AD_file_item = array();
            for ($i = 0; $i < count($ADList); $i++) {
                $ad_file_array = MailController::arrayIfNull($ADList[$i], ['seq_no'], -1, true);
                if ($ad_file_array > 0) {
                    array_push($G00000_1['C00800_18'], $ad_file_array);
                    $AD_file_item['file_path'] = MailController::arrayIfNull($ADList[$i], ['file_path']);
                    $AD_file_item['name'] = MailController::arrayIfNull($ADList[$i], ['file_name']);
                    $file_size = MailController::arrayIfNull($ADList[$i], ['file_size']);
                    if ($file_size != null) {
                        $AD_file_item['size'] = (int)$file_size;
                    }
                    $AD_file_item['adPosition'] = "";
                    $AD_file_item['seq_no'] = MailController::arrayIfNull($ADList[$i], ['seq_no']);
                    array_push($task_result_file_array, $AD_file_item);
                    //存储AD seqNo
//                    $G00000_1['C00800_18'] = $seqNo;
                } else {
                    $G00000_1['C00800_18'] = [];
                }
            }
//            //ADUrl 赋值 start
//            if ($seqNo > 0) {
//                $ADUrl = MailController::arrayIfNull($item, ['C00800_6'], []);
//                $file_item = array();
//                $file_item['file_path'] = MailController::arrayIfNull($ADUrl, ['file_path']);
//                $file_item['name'] = MailController::arrayIfNull($ADUrl, ['file_name']);
//                $file_size = MailController::arrayIfNull($ADUrl, ['file_size']);
//                if ($file_size != null) {
//                    $file_item['size'] = (int)$file_size;
//                }
//                $file_item['adPosition'] = "";
//                $file_item['seq_no'] = MailController::arrayIfNull($ADUrl, ['seq_no']);
//                array_push($task_result_file_array, $file_item);
//                //存储AD seqNo
//                $G00000_1['C00800_18'] = $seqNo;
//            } else {
//                $G00000_1['C00800_18'] = null;
//            }
            //配信日
            $G00000_1['C00700_4'] = MailController::arrayIfNull($item, ['C00700_4']);
            //配信时间
            $G00000_1['C00100_5'] = MailController::arrayIfNull($item, ['C00100_5']);
            //备注
            $G00000_1['C00200_8'] = MailController::arrayIfNull($item, ['C00200_8']);
            $data_format_day = date('n月j日', strtotime($G00000_1['C00700_4']));
            $data_format_time = date('G:i', strtotime($G00000_1['C00100_5']));
            $data_format_week = date('w', strtotime($G00000_1['C00700_4']));
            switch ($data_format_week) {
                case 1:
                    $data_format_week = '(月)';
                    break;
                case 2:
                    $data_format_week = '(火)';
                    break;
                case 3:
                    $data_format_week = '(水)';
                    break;
                case 4:
                    $data_format_week = '(木)';
                    break;
                case 5:
                    $data_format_week = '(金)';
                    break;
                case 6:
                    $data_format_week = '(土)';
                    break;
                case 0:
                    $data_format_week = '(日)';
                    break;
            }
            $G00000_1['C00100_28']=$data_format_day.$data_format_week.$data_format_time;
            //ADUrl 赋值 end
            //LPList 赋值 start
            $LPList = MailController::arrayIfNull($item, ['G00800_7'], []);
            //存储LPList seqNo
            $G00000_1['C00800_19'] = array();
            $G00000_1['C00200_20'] = array();
            $file_item = array();

            for ($i = 0; $i < count($LPList); $i++) {
                array_push($G00000_1['C00200_20'], MailController::arrayIfNullFail($LPList[$i], ['adPosition']));
                $lp_file_array = MailController::arrayIfNull($LPList[$i], ['seq_no'], -1, true);
                if ($lp_file_array > 0) {
                    array_push($G00000_1['C00800_19'], $lp_file_array);
                    $file_item['name'] = MailController::arrayIfNullFail($LPList[$i], ['file_name']);
                    $file_item['file_path'] = MailController::arrayIfNullFail($LPList[$i], ['file_path']);
                    $file_item['adPosition'] = MailController::arrayIfNullFail($LPList[$i], ['adPosition']);
                    $file_item['seq_no'] = MailController::arrayIfNullFail($LPList[$i], ['seq_no']);
                    array_push($task_result_file_array, $file_item);
                } else {
                    array_push($G00000_1['C00800_19'], null);
                }
            }
            $G00000_1['C00100_27'] = count($LPList);
            $G00000_1['C00100_21'] = MailController::arrayIfNull($item, ['C00700_4'])." ";
            $G00000_1['C00100_21'].=MailController::arrayIfNull($item, ['C00100_5']);
            array_push($db_content_array['G00000_1'], $G00000_1);
        }
        //最大配信时间
        $max_time = "";
        //最小配信时间
        $minimum_time = "";
        if (count($item_array_AD) < 1) {
            array_push($db_content_array['G00000_1'], $G00000_1);
        } else {
            $request_id = $this->request_id;
            //$db_content_array['C00100_22'] = 'アカウント名';
            $db_content_array['C00100_22'] =self::getTitleAccount($request_id)->value;
            $db_content_array['C00100_23'] = array();
            //$db_content_array['C00100_26']='カテゴリ';
            $db_content_array['C00100_26']=self::getCategory($request_id)->value;
            foreach ($db_content_array['G00000_1'] as $item) {
                $time = MailController::arrayIfNull($item, ['C00100_21']);
                $dt = $time;
                $min_time = $time;
                array_push($db_content_array['C00100_23'], $time);
                if (strtotime($dt)>strtotime($max_time)) {
                    $max_time = $dt;
                }
                if (strtotime($min_time)<strtotime($minimum_time)||$minimum_time=="") {
                    $minimum_time = $min_time;
                }
            }
        }
        $db_content_array['C00100_24'] = $max_time;
        $db_content_array['C00100_25'] = $minimum_time;
//        for ($j = 0; $j < )
        return [
            'task_result_array' => $db_content_array,
            'task_result_file_array' => $task_result_file_array
        ];
    }

    /**
     * 排他制御.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @throws \Exception 排他失败
     */
    private function exclusiveTask(int $task_id, int $user_id)
    {
        $query = Task::where('id', $task_id)
            ->where('user_id', $user_id)
            ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
            ->where('is_active', \Config::get('const.FLG.ACTIVE'))
            //            ->where('updated_at', '<', $this->task_started_at)
            ->lockForUpdate();
        $task = $query->first();
        if ($task === null) {
            throw new \Exception("The task does not exist or is completed, task_id:$task_id user_id:$user_id");
        }
    }

    /**
     * 成功メッセージを返す.
     * @param null|array|mixed $data オブジェクトを返す
     * @return \Illuminate\Http\JsonResponse 成功メッセージ
     */
    private function success($data = null)
    {
        $message = ['result' => 'success', 'err_message' => '', 'data' => $data];
        return response()->json($message);
    }

    /**
     * 获取ファイル添付.
     * @param int $task_id 作業ID
     * @return mixed ファイル添付
     */
    public function generateDefaultAttachments(int $task_id)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);

        $fileArray = array();
        $attachments = RequestMailAttachment::where('request_mail_id', $request_mail_po->id)->get();
        $max_seq_no = 0;
        // 遍历所有file
        foreach ($attachments as $file) {
            //生成，增加到新文件数组
            $attach_file = array();
            $attach_file['file_seq_no'] = $max_seq_no;
            $attach_file['file_name'] = $file->name;
            $attach_file['file_path'] = $file->file_path;
            $attach_file['size'] = $file->size;
            $attach_file['width'] = $file->width;
            $attach_file['height'] = $file->height;
            array_push($fileArray, $attach_file);
            $max_seq_no++;
        }
        return $fileArray;
    }

    /**
     * 作業内容を保存する.
     * @param array $content 作業内容
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $work_time 作業時間
     * @throws \Throwable 保存に失敗しました
     */
    private function taskSave(array $content, array $task_result_file_array, array $work_time = null)
    {
        $type =  MailController::arrayIfNull($content, ['G00000_1',0,'C00400_2'], false);
        if ($type==true) {
            $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.CANCEL');
        } else {
            $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.DONE');
        }
        \DB::beginTransaction();
        try {
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // 対応表の生成
            //$excel_file = self::checkResultExcel($content, $task_result_file_array);
            // メールの登録
            //$send_mail = self::registerMail($content, $task_result_file_array, $excel_file);
            // タスク実績の中に入れる
            //$content['results']['mail_id'] = [$send_mail->id];

            // タスクのステータスを完了に更新
            $task = Task::findOrFail($this->task_id);
            $task->status = config('const.TASK_STATUS.DONE');
            $task->updated_user_id = \Auth::user()->id;
            $task->save();

            // タスク実績
            $task_result = new TaskResult;
            $task_result->task_id = $this->task_id;
            $task_result->step_id = $this->step_id;
            // 作業時間は手入力値
            // TODO: 手入力する枠が画面にない場合
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null, 'remark' => null];
            }
            $task_result->started_at = $work_time['started_at'];
            $task_result->finished_at = $work_time['finished_at'];
            $task_result->work_time = $work_time['total'];
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $task_result->work_time_comment = $work_time['remark'];
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();
            // タスク実績（ファイル）
            foreach ($task_result_file_array as $task_result_file) {
                $adPosition = MailController::arrayIfNull($task_result_file, ['adPosition'], []);
                if ($adPosition!="") {
                    //获取文件扩展名
                    $file_extension = MailController::arrayIfNull($task_result_file, ['name'], []);
                    $extension = explode(".", $file_extension);
                    //需要被替换的字符  \/:*?"<>|
                    $vowels = array( '/', ':', '*', '"', '<', '>', '|', '?','\\');
                    $file_names = $adPosition;
                    $Lp_file_name = str_replace($vowels, '', $file_names);
                    $task_result_file['name'] = "LP_[".$Lp_file_name."]".".".$extension[1];
                }
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->task_result_id = $task_result->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }
            // 処理キュー登録（承認）
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
            $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = \Auth::user()->id;
            $queue->updated_user_id = \Auth::user()->id;
            $queue->save();

            // ログ登録
            $request_log_attributes = [
                'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
                'request_id' => $this->request_id,
                'request_work_id' => $this->request_work_id,
                'task_id' => $this->task_id,
                'created_user_id' => \Auth::user()->id,
                'updated_user_id' => \Auth::user()->id,
            ];
            $this->storeRequestLog($request_log_attributes);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 失敗したメッセージを返す.
     * @param string $errorMsg エラーメッセージ
     * @return \Illuminate\Http\JsonResponse エラーメッセージ
     */
    private function error(string $errorMsg)
    {
        $message = ['result' => 'error', 'err_message' => $errorMsg];
        return response()->json($message);
    }

    /**
     * 通过作業Id，获取依頼メール
     * @param int $task_id 作業Id
     * @return mixed
     */
    private function getEmailByTaskId(int $task_id)
    {
        $result = \DB::table('request_mails')
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
        return $result;
    }

    /**
     * メールの件名を生成.
     * @param array $data 件名の内容
     * @return string メールの件名
     */
    private static function generateSendMailSubject(array $data): string
    {
        $subject = $data['is_check_decision'] . '【' . $data['business_name'] . '】' . $data['mail_type'] . ' Re:' . $data['request_name'];

        return $subject;
    }

    /**
     * zipファイルを作成.
     * @param string $business_id 業務ID
     * @param array $src_file_array ソースファイル
     * @param string $zip_file_name zipファイル名
     * @param bool $upload_to_s3 生成されたzipファイルは、s3サービスにアップロードするかどうか
     * @return array ファイル情報
     * @throws \Throwable  zipに失敗しました
     */
    private function createZipFile(string $business_id, array $src_file_array, string $zip_file_name, bool $upload_to_s3 = false): array
    {
        // 管理用配列
        $manage_array = [];
        try {
            // ファイルをダウンロード
            foreach ($src_file_array as $file) {
                $file_path = $file['file_path'];
                $file_name = $file['check_file_name'];
                $disk = \Storage::disk(\Config::get('filesystems.cloud'));
                $file = $disk->get($file_path);

                // S3の完全URLを取得
                $url = $disk->url($file_path);
                // S3上に指定ファイルが存在するか確認
                if (!$disk->exists($file_path)) {
                    throw new \Exception('S3Download is failed. File not exists');
                }
                $file_name = isset($file_name) ? $file_name : basename($url);

                // ローカルに一時保存
                $local_disk = \Storage::disk('public');
                $microtime_float = explode('.', (microtime(true)) . '.');
                $tmp_file_name = $microtime_float[0] . $microtime_float[1] . $file_name;
                $local_file_path = 'tmp/' . $tmp_file_name;
                $local_disk->put($local_file_path, $disk->get($file_path));
                $tmp_file_path = storage_path() . '/app/public/' . $local_file_path;
                array_push($manage_array, ['file_name' => $file_name, 'file_full_path' => $tmp_file_path, 'local_disk_path' => $local_file_path]);
            }

            // zipファイルの作成
            $zip = new ZipArchive();
            // ZIPファイルをオープン
            $microtime_float = explode('.', (microtime(true)) . '.');
            $tmp_zip_file_name = $microtime_float[0] . $microtime_float[1] . $zip_file_name;
            $tmp_local_file_path = 'tmp/' . $tmp_zip_file_name;
            $tmp_zip_file_path = storage_path() . '/app/public/' . $tmp_local_file_path;
            $res = $zip->open($tmp_zip_file_path, ZipArchive::CREATE);

            // zipファイルのオープンに成功した場合
            if ($res === true) {
                foreach ($manage_array as $file) {
                    // 圧縮するファイルを指定する
                    $zip->addFile($file['file_full_path'], $file['file_name']);
                }
                // ZIPファイルをクローズ
                $zip->close();
            } else {
                throw new \Exception("Failed to open zip file");
            }
            $local_disk = \Storage::disk('public');
            $file_content = $local_disk->get($tmp_local_file_path);
            if ($upload_to_s3) {
                // ファイルアップロード
                $upload_data = [
                    'business_id' => $business_id,
                    'file' => [
                        'file_name' => $zip_file_name,
                        'file_data' => $file_content,
                    ]
                ];
                $zip_file = $this->uploadFile($upload_data, 'content', true);
                // $task_result_file['download_url'] = url('utilities/download_allow_file?file_path=' . urlencode($task_result_file['file_path']) . '&file_name=' . urlencode($task_result_file['name']));
                // 一時ファイルを削除
                array_push($manage_array, ['file_name' => $tmp_zip_file_name, 'file_full_path' => $tmp_zip_file_path, 'local_disk_path' => $tmp_local_file_path]);
            } else {
                $mime_type = \File::mimeType($tmp_zip_file_path);
                $file_size = filesize($tmp_zip_file_path);
                $zip_file = [
                    'disk' => $local_disk,
                    'file_name' => $tmp_zip_file_name,
                    'file_path' => $tmp_local_file_path,
                    'url' => null,
                    'src' => null,
                    'file_size' => $file_size,
                    'mime_type' => $mime_type
                ];
            }
            foreach ($manage_array as $file) {
                $local_disk = \Storage::disk('public');
                $local_disk->delete($file['local_disk_path']);
            }
            return $zip_file;
        } catch (\Throwable $th) {
            // 一時ファイルを削除
            foreach ($manage_array as $file) {
                $local_disk = \Storage::disk('public');
                $local_disk->delete($file['local_disk_path']);
            }
            throw $th;
        }
    }

    /**
     * ファイルをs3にアップロードする.
     * @param array $data アップロードするすべてのファイルを含む配列
     * @param string $type base64:ファイルコンテンツはbase64で取得されます, content:ファイルの内容はバイト単位で取得されます
     * @param bool $return_detail_info 詳細を返すかどうか(file_size,mini_type,src)
     * @return array (file_name, file_path, url, file_size, mime_type) ファイル情報
     * @throws Exception アップロードに失敗しました
     */
    private static function uploadFile(array $data, string $type = 'content', bool $return_detail_info = false): array
    {
        $business_id = $data['business_id'];
        $file = $data['file'];

        $file_name = $file['file_name'];
        $file_path = 'task_result_files/' . $business_id . '/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;
        switch ($type) {
            case 'base64':
                // file data is decode to base64
                list(, $fileData) = explode(';', $file['file_data']);
                list(, $fileData) = explode(',', $fileData);
                $file_contents = base64_decode($fileData);
                break;
            case 'content':
                $file_contents = $file['file_data'];
                break;
            default:
                throw new Exception('not_type');
        }

        $url = Uploader::uploadToS3($file_contents, $file_path);
        if ($return_detail_info) {
            list($src, $mime_type, $file_size, $data) = CommonDownloader::base64FileFromS3($file_path);
        } else {
            $mime_type = null;
            $file_size = null;
            $src = null;
        }
        return array(
            'file_name' => $file_name,
            'file_path' => $file_path,
            'url' => $url,
            'display_size' => '', //TODO 課題ADTAKT_PF-4
            'file_size' => $file_size,
            'mime_type' => $mime_type,
            'src' => $src
        );
    }
    /**
     * 配列のNULL処理
     * @param array $array 存储AD数组
     * @param bool $type 判断 配信なし
     * @return mixed
     */
    public function validationData(array $array, bool $type)
    {
        for ($i = 0; $i < count($array); $i++) {
            $row = $i + 1;
            if ($type == false) {
                if ($array[$i]['C00700_4'] == null) {
                    throw new \Exception(" $row 配信日を入力してください。");
                }
                if ($array[$i]['C00100_5'] == null) {
                    throw new \Exception(" $row 配信時間を入力してください。");
                }
                //校验AD下的checkbox
                if ($array[$i]['C00400_9'] == false) {
                    throw new \Exception(" $row 「アカウント名、配信日・時間がキャプチャ内に入っています」を選択してください。");
                }
                if ($array[$i]['C00400_10']==false) {
                    throw new \Exception(" $row 「左記のアカウント・上記の取得対象に一致しています」を選択してください。");
                }
                if ($array[$i]['C00400_11']==false) {
                    throw new \Exception(" $row 「下のバーは文字が入力できる状態です」を選択してください。");
                }
                if ($array[$i]['C00400_12']==false) {
                    throw new \Exception(" $row 「ほかの日のメッセージは映り込んでいません」を選択してください。");
                }
                if ($array[$i]['C00400_13']==false) {
                    throw new \Exception(" $row 「（動画・横スクロールAD）マニュアルを確認しました」を選択してください。");
                }
                //校验LP下的checkbox
                if ($array[$i]['C00400_14']==false) {
                    throw new \Exception(" $row 「ファーストビューのみ取得しました」を選択してください。");
                }
                if ($array[$i]['C00400_15']==false) {
                    throw new \Exception(" $row 「権限許可の画面ではありません」を選択してください。");
                }
                if ($array[$i]['C00400_16']==false) {
                    throw new \Exception(" $row 「ロードが終わってから取得しました」を選択してください。");
                }
                if ($array[$i]['C00400_17']==false) {
                    throw new \Exception(" $row 「再生・ダウンロードバーなどが映り込んでいません」を選択してください。");
                }
                // AD画像素材ファイル
                $ad_seq_no_list = MailController::arrayIfNull($array[$i], ['C00800_6'], []);
                //$ad_seq_no = MailController::arrayIfNullFail($array[$i], ['C00800_6'], " $row 番目のADキャプチャーはアップロードされません。", true);
                if (count($ad_seq_no_list) > 0) {
                    for ($j = 0; $j<count($ad_seq_no_list); $j++) {
                        MailController::arrayIfNullFail($ad_seq_no_list[$j], ['seq_no'], " $row 番目のADキャプチャーはアップロードされません。", true);
                        //throw new \Exception(" $row 番目のADキャプチャーはアップロードされません。");
                    }
                } else {
                    throw new \Exception(" $row 番目のADキャプチャーはアップロードされません。");
                }
                $lp_seq_no_array = MailController::arrayIfNull($array[$i], ['G00800_7'], []);
                if (count($lp_seq_no_array) > 0) {
                    for ($j = 0; $j<count($lp_seq_no_array); $j++) {
                        // LP画像素材ファイル
                        $lp_seq_no = MailController::arrayIfNullFail($lp_seq_no_array[$j], ['seq_no'], " $row 番目のLPキャプチャーはアップロードされません。", true);
                        // LP 的AD位置
                        $lp_seq_no = MailController::arrayIfNullFail($lp_seq_no_array[$j], ['adPosition'], " $row 番目のAD位置を入力してください。", true);
                    }
                }
            }
            if ($type == true) {
                //校验配信日是否为空
                if ($array[$i]['C00700_4'] != null) {
                    throw new \Exception(" $row 配信なしの場合、配信日は入力できません。");
                }
                //校验配信时间是否为空
                if ($array[$i]['C00100_5'] != null) {
                    throw new \Exception("$row 配信なしの場合、配信時間は入力できません。");
                }
                if ($array[$i]['C00200_8'] != "") {
                    throw new \Exception(" $row 配信なしの場合、備考は入力できません。");
                }
                if ($array[$i]['C00400_9'] != false) {
                    throw new \Exception(" $row 「アカウント名、配信日・時間がキャプチャ内に入っています」を選択しないでください。");
                }
                if ($array[$i]['C00400_10'] != false) {
                    throw new \Exception(" $row 「左記のアカウント・上記の取得対象に一致しています」を選択しないでください。");
                }
                if ($array[$i]['C00400_11'] != false) {
                    throw new \Exception(" $row 「下のバーは文字が入力できる状態です」を選択しないでください。");
                }
                if ($array[$i]['C00400_12'] != false) {
                    throw new \Exception(" $row 「ほかの日のメッセージは映り込んでいません」を選択しないでください。");
                }
                if ($array[$i]['C00400_13'] != false) {
                    throw new \Exception(" $row 「（動画・横スクロールAD）マニュアルを確認しました」を選択しないでください。");
                }
                //校验LP下的checkbox
                if ($array[$i]['C00400_14'] != false) {
                    throw new \Exception(" $row 「ファーストビューのみ取得しました」を選択しないでください。");
                }
                if ($array[$i]['C00400_15'] != false) {
                    throw new \Exception(" $row 「権限許可の画面ではありません」を選択しないでください。");
                }
                if ($array[$i]['C00400_16'] != false) {
                    throw new \Exception(" $row 「ロードが終わってから取得しました」を選択しないでください。");
                }
                if ($array[$i]['C00400_17'] != false) {
                    throw new \Exception(" $row 「再生・ダウンロードバーなどが映り込んでいません」を選択しないでください。");
                }
                // 画像素材ファイル
                //$seqNo = MailController::arrayIfNull($array[$i], ['C00800_6','seq_no'], -1, true);
                $ad_seq_no_list = MailController::arrayIfNull($array[$i], ['C00800_6'], []);
                if (count($ad_seq_no_list) > 0) {
                    throw new \Exception(" $row 配信なしの場合、ADキャプチャーはアップロードできません。");
                }
                $acount_lp_array =MailController::arrayIfNull($array[$i], ['G00800_7'], [], true);
                if (count($acount_lp_array)>0) {
                    for ($k = 0; $k<count($acount_lp_array); $k++) {
                        $acount_lp =MailController::arrayIfNull($acount_lp_array[$k], ['seq_no'], -1, true);
                        $acount_lp_remarks =MailController::arrayIfNull($acount_lp_array[$k], ['adPosition'], -1, true);
                        if ($acount_lp>0) {
                            throw new \Exception(" $row 配信なしの場合、LPキャプチャーはアップロードできません。");
                        }
                        if ($acount_lp_remarks>0) {
                            throw new \Exception(" $row 配信なしの場合、AD位置は入力できません。");
                        }
                    }
                }
            }
        }
    }

    /**
 * 通过作業Id，获取依頼メール
 * @param int $request_id 作業Id
 * @return mixed
 */
    private function getTitleAccount(int $request_id)
    {
        $result = \DB::table('requests')
            ->select(
                'order_detail_values.value'
            )
            ->join('order_details_requests', 'requests.id', '=', 'order_details_requests.request_id')
            ->join('order_details', 'order_details_requests.order_detail_id', '=', 'order_details.id')
            ->join('order_detail_values', 'order_details.id', '=', 'order_detail_values.order_detail_id')
            ->join('order_file_import_column_configs_order_detail_values', 'order_detail_values.id', '=', 'order_file_import_column_configs_order_detail_values.order_detail_value_id')
            ->join('order_file_import_column_configs', 'order_file_import_column_configs_order_detail_values.order_file_import_column_config_id', '=', 'order_file_import_column_configs.id')
            ->where('requests.id', $request_id)
            ->where('order_file_import_column_configs.item', 'アカウント名')
            ->first();
        return $result;
    }

    /**
     * 通过作業Id，获取类别
     * @param int $request_id 作業Id
     * @return mixed
     */
    private function getCategory(int $request_id)
    {
        $result = \DB::table('requests')
            ->select(
                'order_detail_values.value'
            )
            ->join('order_details_requests', 'requests.id', '=', 'order_details_requests.request_id')
            ->join('order_details', 'order_details_requests.order_detail_id', '=', 'order_details.id')
            ->join('order_detail_values', 'order_details.id', '=', 'order_detail_values.order_detail_id')
            ->join('order_file_import_column_configs_order_detail_values', 'order_detail_values.id', '=', 'order_file_import_column_configs_order_detail_values.order_detail_value_id')
            ->join('order_file_import_column_configs', 'order_file_import_column_configs_order_detail_values.order_file_import_column_config_id', '=', 'order_file_import_column_configs.id')
            ->where('requests.id', $request_id)
            ->where('order_file_import_column_configs.item', 'カテゴリ')
            ->first();
        return $result;
    }
}
