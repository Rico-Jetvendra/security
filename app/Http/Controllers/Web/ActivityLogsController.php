<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLogs;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogsController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'User', 'field' => 'username'],
            ['label' => 'Module', 'field' => 'module'],
            ['label' => 'Action', 'field' => 'action'],
            ['label' => 'Subject Type', 'field' => 'subject_type'],
            ['label' => 'Subject ID', 'field' => 'subject_id'],
            ['label' => 'Created Date', 'field' => 'created_at'],
        ];

        return view('pages.activity_logs.index', compact('data', 'columns'));
    }

    public function data(){
        $query = $this->getSql()->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('username', function($row){
                $text = '
                    <span class="text-primary clickable-log"
                        style="cursor:pointer;"
                        data-row=\''.json_encode($row).'\'>
                        '.$row->username.'
                    </span>
                ';
                return $text;
            })
            ->addColumn('created_at', function($row){
                return Carbon::parse($row->created_at)->format('d F Y H:i:s');
            })
            ->rawColumns(['username', 'action'])
            ->make(true);
    }

    public function view($id){
        $data = $this->getSql()->where('t_activity_logs.activity_id', $id)->firstOrfail();
        if(!$data){
            return redirect()->back()->with('error', 'User not found!');
        }

        return $data;
    }

    private function getSql(){
        $sql = ActivityLogs::join('tbl_users as us', 'us.id', 't_activity_logs.user_id')
                            ->select(
                                't_activity_logs.*',
                                'us.username'
                            );

        return $sql;
    }
}
