<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class Application extends Model{
    protected $connection   = 'mysql';
    protected $table        = 't_application';
    protected $primaryKey   = 'application_id';
    public $timestamps      = false;

    protected $whitelist = [
        "application_name",
        "status",
    ];
    protected $defaultSort = 'created_date';

    protected $fillable = [
        "application_id",
        "application_name",
        "application_type",
        "status",
        "created_date",
        "created_by",
        "updated_date",
        "updated_by",
        "deleted_date",
        "deleted_by",
    ];

    public function getWhitelist(){
        return $this->whitelist;
    }

    public function getDefaultSort(){
        return $this->defaultSort;
    }

    protected static function booted(){
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('t_application.status', 1);
        });

        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('t_application.created_date', 'desc');
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
