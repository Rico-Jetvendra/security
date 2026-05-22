<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserRole extends Model{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 't_user_roles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $whitelist = [
        "user_id",
        "role_id",
    ];
    protected $defaultSort = 'created_at';

    protected $fillable = [
        "user_id",
        "role_id",
        "application_id",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "deleted_at",
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
            $builder->where('t_user_roles.status', 1)->where('t_user_roles.deleted_at', null);
        });

        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('t_user_roles.created_at', 'desc');
        });

        static::creating(function ($model) {
            $model->created_by = session('user_security')->id ?? 1;
            $model->created_at = now();
        });

        static::updating(function ($model) {
            $model->updated_by = session('user_security')->id ?? 1;
            $model->updated_at = now();
        });
    }
}
