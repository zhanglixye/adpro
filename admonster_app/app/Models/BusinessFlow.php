<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessFlow extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id',
        'business_id',
        'next_step_id',
        'seq_no',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 前作業の作業ID配列を返却する
     *
     * @param integer $step_id
     * @return mixed
     */
    public static function beforeStepIds(int $step_id)
    {
        return self::where('next_step_id', $step_id)
            ->orderBy('seq_no')
            ->pluck('step_id');
    }

    /* -------------------- relations ------------------------- */

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
