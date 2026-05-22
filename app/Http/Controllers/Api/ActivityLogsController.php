<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLogs;
use Illuminate\Http\Request;

class ActivityLogsController extends Controller{

    public function store(Request $request){
        try {
            ActivityLogs::create([
                'user_id'       => $request->user_id,
                'module'        => $request->module,
                'action'        => $request->action,

                'description'   => $request->description,
                'ip_address'    => $request->ip_address,
                'user_agent'    => $request->user_agent,

                'old_values'    => $request->old_values,
                'new_values'    => $request->new_values,

                'subject_type'  => $request->subject_type,
                'subject_id'    => $request->subject_id,

                'created_at'    => now()

            ]);

            return response()->json(['status'=>'Success', 'message' => 'Log inserted']);
        } catch (\Exception $e) {
            return response()->json(['status'=>'Failed', 'message' => 'Failed to insert a log with error: '.$e->getMessage()]);
        }
    }
}
