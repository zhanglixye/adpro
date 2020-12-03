<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ItemConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'step_id',
        'sort',
        'label_id',
        'item_key',
        'item_type',
        'use_screen',
        'option',
        'layout_option',
        'validate_option',
        'is_required',
        'diff_check_level',
        'is_deleted',
        'created_user_id',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /* -------------------- relations ------------------------- */
    public function itemConfigValues()
    {
        return $this->hasMany(ItemConfigValue::class);
    }
}
