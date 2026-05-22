<?php

namespace App\Services;

use App\Models\ActivityLogs;
use Illuminate\Support\Facades\Log;

class ActivityLogger{
    public static function create($data){
        self::log([
            "module"        => "Security",
            'action'        => 'CREATE',
            'description'   => 'Created new ' . strtolower($data['subject_type']),
            'subject_type'  => $data['subject_type'],
            'subject_id'    => $data['subject_id'],
            'new_values'    => $data['new_values']
        ]);
    }

    public static function update($data){
        self::log([
            "module"        => "Security",
            'action'        => 'UPDATE',
            'description'   => 'Updated ' . strtolower($data['subject_type']),
            'subject_type'  => $data['subject_type'],
            'subject_id'    => $data['subject_id'],
            'new_values'    => $data['new_values'],
            'old_values'    => $data['old_values'],
        ]);
    }

    public static function delete($data){
        self::log([
            "module"        => "Security",
            'action'        => 'DELETE',
            'description'   => 'Deleted ' . strtolower($data['subject_type']),
            'subject_type'  => $data['subject_type'],
            'subject_id'    => $data['subject_id'],
            'old_values'    => $data['old_values'],
            'new_values'     => [
                'status' => 0,
                'deleted_date' => now(),
                'deleted_by' => session('user_security')->id ?? 1
            ]
        ]);
    }

    public static function login($description = null){
        self::log([
            'module'        => 'Security',
            'action'        => 'LOGIN',
            'description'   => $description ?? 'User logged in'
        ]);
    }

    public static function logout($description = null){
        self::log([
            'module'        => 'Security',
            'action'        => 'LOGOUT',
            'description'   => $description ?? 'User logged out'
        ]);
    }

    public static function failedLogin($email = null, $description = null){
        self::log([
            'module' => 'Security',
            'action' => 'FAILED_LOGIN',

            'description' => $description ?? 'Failed login attempt',

            'new_values' => [
                'email' => $email
            ]
        ]);
    }

    public static function log(array $data){
        try {
            ActivityLogs::create([
                'user_id'       => session('user_security')->id ?? null,
                "module"        => "Security",
                "action"        => $data['action'] ?? null,
                "description"   => $data['description'] ?? null,

                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent(),

                'old_values'    => $data['old_values'] ?? null,
                'new_values'    => $data['new_values'] ?? null,

                'subject_type'  => $data['subject_type'] ?? null,
                'subject_id'    => $data['subject_id'] ?? null,

                'created_at'    => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Activity Logger Error: ' . $e->getMessage());
        }
    }
}
