<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLogs extends Model{
    protected $table = 't_activity_logs';
    protected $primaryKey = 'activity_id';
    public $timestamps = false;

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'subject_type',
        'subject_id',
        'created_at',
    ];

    public function getOldValuesFormattedAttribute(){
        return json_encode($this->old_values, JSON_PRETTY_PRINT);
    }

    public function getNewValuesFormattedAttribute(){
        return json_encode($this->new_values, JSON_PRETTY_PRINT);
    }

    protected static function booted(){
        static::addGlobalScope('latest', function ($query) {
            $query->orderBy('t_activity_logs.created_at', 'desc');
        });

        static::creating(function ($model) {
            $model->created_at = now();
        });

    }
}
