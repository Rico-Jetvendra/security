<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable{
    protected $connection = 'mysql';
    protected $table = 'tbl_users';
    public $timestamps = false;

    protected $whitelist = [
        "username",
        "email",
        "password",
        "kode_sales",
        "otp",
        "status",
        "active",
        "device_token",
        "app_version",
        "token",
        "token_expiry",
        "no_hp",
        "photo_profile",
    ];
    protected $defaultSort = 'id';

    protected $fillable = [
        'username',
        'email',
        'status',
        'active',
        'app_version',
        'token',
        'token_expiry',
        'kode_sales',
        'login_date',
        'device_token',
        'password',
        'otp',
        'application_id',
        'no_hp',
        'photo_profile',
        'created_date',
        'created_by',
        'updated_date',
        'updated_by',
        'deleted_date',
        'deleted_by',
    ];

    public function getPhotoProfileAttribute($value){
        return url('storage/' . $value);
    }

    public function getWhitelist(){
        return $this->whitelist;
    }

    public function getDefaultSort(){
        return $this->defaultSort;
    }

    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    protected static function booted(){
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('tbl_users.status', 1)->where('tbl_users.deleted_date', null);
        });

        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('tbl_users.created_date', 'desc');
        });

        static::creating(function ($model) {
            $model->created_by = session('user_security')->id ?? 1;
            $model->created_date = now();
        });

        static::updating(function ($model) {
            $model->updated_by = session('user_security')->id ?? 1;
            $model->updated_date = now();
        });
    }
}

?>
