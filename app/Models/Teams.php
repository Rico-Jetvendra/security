<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Teams extends Model{
    protected $table        = 't_teams';
    protected $primaryKey   = 'teams_id';
    public $timestamps      = false;

    protected $fillable = [
        'teams_name',
        'teams_description',
        'teams_parent',
        'status',
        'created_date',
        'created_by',
        'updated_date',
        'updated_by',
        'deleted_date',
        'deleted_by',
    ];

    protected $casts = [
        'teams_id' => 'integer',
        'teams_parent' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'deleted_date' => 'datetime',
    ];

    protected static function booted(){
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('t_teams.status', 1);
        });

        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('t_teams.created_date', 'desc');
        });

        static::creating(function ($model) {
            $model->created_by      = session('user_security')->id ?? 1;
            $model->created_date    = now();
        });

        static::updating(function ($model) {
            $model->updated_by      = session('user_security')->id ?? 1;
            $model->updated_date    = now();
        });
    }
}
