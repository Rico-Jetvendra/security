<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogsController extends Controller{

    public function store(Request $request){
        try {
            ActivityLogs::create([
                'user_id'       => $request->user_id ?? null,
                'module'        => $request->module ?? null,
                'action'        => $request->action ?? null,

                'description'   => $request->description ?? null,
                'ip_address'    => $request->ip_address ?? null,
                'user_agent'    => $request->user_agent ?? null,

                'old_values'    => $request->old_values ?? null,
                'new_values'    => $request->new_values ?? null,

                'subject_type'  => $request->subject_type ?? null,
                'subject_id'    => $request->subject_id ?? null

            ]);

            return response()->json(['status'=>'Success', 'message' => 'Log inserted']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status'=>'Failed', 'message' => 'Failed to insert a log with error: '.$e->getMessage()]);
        }
    }
}
