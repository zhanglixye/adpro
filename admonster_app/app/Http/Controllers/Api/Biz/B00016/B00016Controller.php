<?php

namespace App\Http\Controllers\Api\Biz\B00016;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Http\Controllers\Controller;
use App\Models\TaskResult;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class B00016Controller extends Controller
{
    public function lineCaptureReport(Request $req)
    {
        try {
            //抽出条件の取得
            $business = $req->business_id;//業務ID
            $started_at = $req->started_at;//期間From
            $finished_at = $req->finished_at;//期間To
//            $started_at = $req->startTime;
//            $finished_at = $req->endTime;
            //ファイル作成
            $report_files = array();
            $report_files[] = self::makeReport($started_at, $finished_at);

            // 終了
            return response()->json([
                'result' => 'success',
                'report_files' => $report_files
            ]);
        } catch (\Exception $e) {
            // エラーあり
            report($e);
            return response()->json([
                'result' => 'error',
                'err_message' => ''
            ]);
        }
    }
    /**
     * レポート出力
     * @param string $started_at 期間From
     * @param string $finished_at 期間To
     * @return array report file
     */
    private function makeReport(string $started_at, string $finished_at): array
    {
        $user_timezone_started_at = parse_utc_string_to_user_timezone_date($started_at);//期間From
        $user_timezone_finished_at = parse_utc_string_to_user_timezone_date($finished_at);//期間To
        //格式化时间
        $user_timezone_started_at = $user_timezone_started_at->format('Y-m-d H:i');
        $user_timezone_finished_at = $user_timezone_finished_at->format('Y-m-d H:i');
        // テンプレートファイルの読み込み
        $template_file_path = storage_path('biz/b00016/template.xlsx');
        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($template_file_path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($template_file_path);
        $worksheet = $spreadsheet->getSheetByName("Sheet1");
        // 対象納品データの集計
        $result = DB::select(
            ' select deliveries.content                             content, ' .
            ' request_works.step_id                                    biz_id ' .
            ' from requests' .
            ' inner join request_works on requests.id = request_works.request_id ' .
            ' inner join approvals on  approvals.request_work_id = request_works.id ' .
            ' inner join approval_tasks on  approvals.id = approval_tasks.approval_id ' .
            ' inner join deliveries on approval_tasks.id = deliveries.approval_task_id '.
            ' where (requests.business_id = 16 or requests.business_id=17) ' .
            ' and (request_works.step_id = 22  or request_works.step_id = 23) ' .
            ' and ((deliveries.content -> \'$.C00100_25\' >= ? and deliveries.content -> \'$.C00100_25\' <= ?) ' .
            ' or (deliveries.content -> \'$.C00100_24\' >= ? and deliveries.content -> \'$.C00100_24\' <= ?) ' .
            ' or (deliveries.content -> \'$.C00100_25\' <= ? and  deliveries.content ->\'$.C00100_24\'>= ?) ' .
            ' or (deliveries.content -> \'$.C00100_25\' <= ? and deliveries.content ->\'$.C00100_24\' >= ?)) ' .
            ' ORDER BY deliveries.content -> \'$.C00100_22\' ',
            [$user_timezone_started_at, $user_timezone_finished_at, $user_timezone_started_at,
                $user_timezone_finished_at,$user_timezone_started_at,$user_timezone_started_at,
                $user_timezone_finished_at,$user_timezone_finished_at]
        );
        //$index = 2;
        //用于存储符合条件的数组
        $task_array = [];
        //过滤符合条件的数据
        foreach ($result as $data) {
            $content_data = $data->content;
            $task_result_array = json_decode($content_data);
//            $task_result_array = (array)$task_result_array;
            //当前作业的当中AD的所有配信时间
            //$C00100_23_array = MailController::arrayIfNull($task_result_array, ['C00100_23'], []);
            $C00100_23_array = $task_result_array->C00100_23;
            for ($i = 0; $i < count($C00100_23_array); $i++) {
                if ($C00100_23_array[$i] > $user_timezone_started_at && $C00100_23_array[$i] < $user_timezone_finished_at) {
                    //获取公司名称
                    $corporate_name = $task_result_array->C00100_22;
                    if (is_object($corporate_name)) {
                        $corporate_name = $corporate_name->value;
                    }
                    //获取LP数量
                    //$lp_array =  MailController::arrayIfNull($task_result_array,['G00000_1'[$i],'C00800_19'],[]);
                    $lp_array =  $task_result_array->G00000_1[$i]->C00800_19;
                    $lp_count = count($lp_array);
                    //获取biz_id
                    $biz_id =$data->biz_id;
                    //获取配信日
                    //$release_day =MailController::arrayIfNull($task_result_array,['G00000_1'[$i],'C00700_4'],[]);
                    $release_day =$task_result_array->G00000_1[$i]->C00700_4;
                    ///获取配信时间
                    //$release_time =   MailController::arrayIfNull($task_result_array,['G00000_1'[$i],'C00100_5'],[]);
                    $release_time =   $task_result_array->G00000_1[$i]->C00100_5;
                    //拼出存储符合条件的数据的key, 格式：公司名称_配信日 配信时间_biz_id
                    $eligible_key = $corporate_name.'_'.
                                    $C00100_23_array[$i].'_'.
                                    $biz_id;
                    $task_array[$eligible_key] = MailController::arrayIfNull($task_array, [$eligible_key], []);
                    //若 $task_key存在 则说明 AD配信时间 相同 只需要加LP数量即可
                    if (count($task_array[$eligible_key])>0) {
                        $task_array[$eligible_key][3] = $task_array[$eligible_key][3]+$lp_count;
                    } else {
                        array_push($task_array[$eligible_key], $corporate_name);
                        array_push($task_array[$eligible_key], $release_day);
                        array_push($task_array[$eligible_key], $release_time);
                        array_push($task_array[$eligible_key], $lp_count);
                        array_push($task_array[$eligible_key], $biz_id);
                    }
                }
            }
        }
        ksort($task_array);
        //往EXCEL中插入数据
        //メッセージ最大数
        $biz_22_max_count = 0;
        //タイムライン最大数
        $biz_23_max_count = 0;
        //メッセージ总数
        $biz_22_count = 0;
        //タイムライン总数
        $biz_23_count = 0;
        //公司名称
        $company_name = "";
        //配信日
        $release_time = "";
//        foreach ($task_array as $value) {
//            if ($value[4] == 22 && $biz_22_count<$value[3]) {
//                $biz_22_count = $value[3];
//            } else if ($value[4] == 23 && $biz_23_count<$value[3]) {
//                $biz_23_count = $value[3];
//            }
//        }
        //遍历出 同公司并且同配信日的 不同配信时间总数
        foreach ($task_array as $value) {
            if ($value[4] == 22 && ($company_name == "" or $company_name == null)) {
                $company_name = $value[0];
                $release_time = $value[1];
                $biz_22_count++;
                $biz_22_max_count = $biz_22_count;
            } else if ($value[4] == 22 && $company_name != "" && $company_name != null) {
                if ($company_name ==  $value[0] && $release_time ==  $value[1]) {
                    $biz_22_count++;
                    if ($biz_22_max_count<$biz_22_count) {
                        $biz_22_max_count = $biz_22_count;
                    }
                } else {
                    $company_name = $value[0];
                    $release_time = $value[1];
                    $biz_22_count = 1;
                }
            } else if ($value[4] == 23  && ($company_name == "" or $company_name == null)) {
                $company_name = $value[0];
                $release_time = $value[1];
                $biz_23_count++;
                $biz_23_max_count = $biz_23_count;
            } else if ($value[4] == 23 && $company_name != "" && $company_name != null) {
                if ($company_name ==  $value[0] && $release_time ==  $value[1]) {
                    $biz_23_count++;
                    if ($biz_23_max_count<$biz_23_count) {
                        $biz_23_max_count = $biz_23_count;
                    }
                } else {
                    $company_name = $value[0];
                    $release_time = $value[1];
                    $biz_23_count = 1;
                }
            }
        }
        //如果总数小于10 则 默认 给10行
        if ($biz_22_max_count<10) {
            $biz_22_max_count = 10;
        }
        if ($biz_23_max_count<10) {
            $biz_23_max_count = 10;
        }
        $biz_22_array = [];
        $biz_22_array_lp = [];
        $biz_23_array = [];
        $biz_23_array_lp = [];
        //需要生成的 title
        $biz_22_title = [];
        $biz_23_title = [];
        $biz_22_title_lp = [];
        $biz_23_title_lp = [];

        for ($m = 2; $m<$biz_22_max_count+2; $m++) {
            array_push($biz_22_array, $m);
        }
        for ($n = 0; $n < $biz_23_max_count; $n++) {
            array_push($biz_23_array, $biz_22_max_count+2+$n);
        }
        for ($q = 0; $q<$biz_22_max_count; $q++) {
            array_push($biz_22_array_lp, count($biz_22_array)+count($biz_23_array)+2+$q);
        }
        for ($r = 0; $r<$biz_23_max_count; $r++) {
            array_push($biz_23_array_lp, count($biz_22_array)+count($biz_23_array)+count($biz_22_array_lp)+2+$r);
        }
        for ($a = 0; $a<count($biz_22_array); $a++) {
            $biz_title = MailController::dynamicMeter($biz_22_array[$a]);
            array_push($biz_22_title, $biz_title);
        }
        for ($b = 0; $b<count($biz_23_array); $b++) {
            $biz_title = MailController::dynamicMeter($biz_23_array[$b]);
            array_push($biz_23_title, $biz_title);
        }
        for ($c = 0; $c<count($biz_22_array_lp); $c++) {
            $biz_title = MailController::dynamicMeter($biz_22_array_lp[$c]);
            array_push($biz_22_title_lp, $biz_title);
        }
        for ($d = 0; $d<count($biz_23_array_lp); $d++) {
            $biz_title = MailController::dynamicMeter($biz_23_array_lp[$d]);
            array_push($biz_23_title_lp, $biz_title);
        }
        //行数
        $row = 1;

        //生成 表头 start
        $worksheet->setCellValue('A' . $row, 'アカウント');
        $worksheet->setCellValue('B' . $row, '配信日');
        for ($f = 0; $f<count($biz_22_title); $f++) {
            $col = $biz_22_title[$f];
            $worksheet->setCellValue($col. $row, 'メッセージ配信時間');
        }
        for ($k = 0; $k<count($biz_23_title); $k++) {
            $col = $biz_23_title[$k];
            $worksheet->setCellValue($col. $row, 'タイムライン配信時間');
        }
        for ($l = 0; $l<count($biz_22_title_lp); $l++) {
            $col = $biz_22_title_lp[$l];
            $worksheet->setCellValue($col. $row, 'メッセージLP数');
        }
        for ($m = 0; $m<count($biz_23_title_lp); $m++) {
            $col = $biz_23_title_lp[$m];
            $worksheet->setCellValue($col. $row, 'タイムラインLP数');
        }
        //生成 表头 end

        //biz 22列数
        $biz_22_index = 0;
        //biz 23列数
        $biz_23_index = 0;
        //biz 22 lp列数
        $biz_22_lp_index = 0;
        //biz 23 lp列数
        $biz_23_lp_index = 0;
        //记录上一条数据公司名称
        $corporate_name_the_previous ='';
        //记录上一条数据配信时间
        $the_previous_time ='';
        //插入数据
        foreach ($task_array as $item) {
            //公司名称
            $corporate_name = $item[0];
            //配信日
            $release_day = $item[1];
            //配信时间
            $release_time = $item[2];
            //lp数
            $lp_count = $item[3];
            //业务id
            $biz_id = $item[4];

            if ($corporate_name_the_previous=='') {
                $row = $row+1;
                $corporate_name_the_previous = $corporate_name;
                $the_previous_time = $release_day;
                if ($biz_id == 22) {
                    //biz_22插入配信时间
                    $worksheet->setCellValue($biz_22_title[$biz_22_index] . $row, $item[2]);
                    //biz_22插入LP数
                    $worksheet->setCellValue($biz_22_title_lp[$biz_22_lp_index] . $row, $item[3]);
                    $biz_22_index++;
                    $biz_22_lp_index++;
                } else {
                    //biz_23插入配信时间
                    $worksheet->setCellValue($biz_23_title[$biz_23_index] . $row, $item[2]);
                    //biz_23插入LP数
                    $worksheet->setCellValue($biz_23_title_lp[$biz_23_lp_index] . $row, $item[3]);
                    $biz_23_index++;
                    $biz_23_lp_index++;
                }
            } else if ($corporate_name_the_previous == $corporate_name) {
                if ($the_previous_time == $release_day) {
                    if ($biz_id == 22) {
                        //biz_22插入配信时间
                        $worksheet->setCellValue($biz_22_title[$biz_22_index] . $row, $item[2]);
                        //biz_22插入LP数
                        $worksheet->setCellValue($biz_22_title_lp[$biz_22_lp_index] . $row, $item[3]);
                        $biz_22_index++;
                        $biz_22_lp_index++;
                    } else {
                        //biz_23插入配信时间
                        $worksheet->setCellValue($biz_23_title[$biz_23_index] . $row, $item[2]);
                        //biz_23插入LP数
                        $worksheet->setCellValue($biz_23_title_lp[$biz_23_lp_index] . $row, $item[3]);
                        $biz_23_index++;
                        $biz_23_lp_index++;
                    }
                } else {
                    $the_previous_time = $release_day;
                    $biz_22_index = 0;
                    $biz_23_index = 0;
                    $biz_22_lp_index = 0;
                    $biz_23_lp_index = 0;
                    $row++;
                    if ($biz_id == 22) {
                        //biz_22插入配信时间
                        $worksheet->setCellValue($biz_22_title[$biz_22_index] . $row, $item[2]);
                        //biz_22插入LP数
                        $worksheet->setCellValue($biz_22_title_lp[$biz_22_lp_index] . $row, $item[3]);
                        $biz_22_index++;
                        $biz_22_lp_index++;
                    } else {
                        //biz_23插入配信时间
                        $worksheet->setCellValue($biz_23_title[$biz_23_index] . $row, $item[2]);
                        //biz_23插入LP数
                        $worksheet->setCellValue($biz_23_title_lp[$biz_23_lp_index] . $row, $item[3]);
                        $biz_23_index++;
                        $biz_23_lp_index++;
                    }
                }
            } else if ($corporate_name_the_previous != $corporate_name) {
                $corporate_name_the_previous = $corporate_name;
                $the_previous_time = $release_day;
                $row ++;
                $biz_22_index = 0;
                $biz_23_index = 0;
                $biz_22_lp_index = 0;
                $biz_23_lp_index = 0;
                if ($biz_id == 22) {
                    //biz_22插入配信时间
                    $worksheet->setCellValue($biz_22_title[$biz_22_index] . $row, $item[2]);
                    //biz_22插入LP数
                    $worksheet->setCellValue($biz_22_title_lp[$biz_22_lp_index] . $row, $item[3]);
                    $biz_22_index++;
                    $biz_22_lp_index++;
                } else {
                    //biz_23插入配信时间
                    $worksheet->setCellValue($biz_23_title[$biz_23_index] . $row, $item[2]);
                    //biz_23插入LP数
                    $worksheet->setCellValue($biz_23_title_lp[$biz_23_lp_index] . $row, $item[3]);
                    $biz_23_index++;
                    $biz_23_lp_index++;
                }
            }
            //插入公司名称
            $worksheet->setCellValue('A' . $row, $corporate_name);
            //插入配信日
            $worksheet->setCellValue('B' . $row, $release_day);
        }
        // ファイルの保存（local）
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $file_name = 'Line業務件数レポート'.'.xlsx';
        $directory_path = storage_path() . '/app/public/';
        $local_file_path = $directory_path . $file_name;
        $writer->save($local_file_path);
        // ファイル作成
        $file = [
            'name' => $file_name,
            'path' => $local_file_path
        ];

        return $file;
    }
}
