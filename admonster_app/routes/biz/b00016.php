<?php

/*
|--------------------------------------------------------------------------
| アド・プロ長春ルーチン業務用
|--------------------------------------------------------------------------
|
*/

/* -------------------- generate from artisan make:auth ------------------------- */

Route::group(['middleware' => 'auth'], function () {


    /* -------------------- api ------------------------- */

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 業務
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            Route::group([ 'prefix' => 'b00016', 'as' => '.b00016'], function () {
                //
                Route::group([ 'prefix' => 's00022', 'as' => '.s00022', 'namespace' => 'B00016' ], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00022Controller@create'])->name('.create');
                    // 素材 結果キャプチャー アップロード
                    Route::post('uploadFile', ['uses' => 'S00022Controller@uploadFileToS3'])->name('.uploadFile');
                    // 保留
                    Route::post('saveWork', ['uses' => 'S00022Controller@saveWork'])->name('.saveWork');
                    // 処理する
                    Route::post('commitWork', ['uses' => 'S00022Controller@commitWork'])->name('.commitWork');
                    // 不明あり
                    Route::post('wrongWork', ['uses' => 'S00022Controller@wrongWork'])->name('.wrongWork');
                    Route::get('/lineCaptureReport/{startTime}/{endTime}', ['uses' => 'B00016Controller@lineCaptureReport'])->name('.lineCaptureReport');
                });
            });
        });
    });
});
