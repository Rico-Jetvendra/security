<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model{
    protected $table        = 't_member';
    protected $primaryKey   = 'member_id';
    public $timestamps      = false;

    protected $fillable = [
        'user_id',
        'teams_id',
        'is_leader',
        "created_date",
        "created_by",
    ];

    protected $casts = [
        'member_id' => 'integer',
        'user_id' => 'integer',
        'teams_id' => 'integer',
        'is_leader' => 'boolean',
    ];

    protected static function booted(){
        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('t_member.created_date', 'desc');
        });

        static::creating(function ($model) {
            $model->created_by      = session('user_security')->id ?? 1;
            $model->created_date    = now();
        });
    }
}
