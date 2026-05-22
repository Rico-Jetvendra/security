<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class LoginHistoryController extends Controller{
    public function index(){
        $data = User::query()->orderBy('login_date', "DESC")->get();

        $columns = [
            ['label' => 'User', 'field' => 'username'],
            ['label' => 'Login Date', 'field' => 'login_date'],
        ];

        return view('pages.login_history.index', compact('data', 'columns'));
    }

    public function data(){
        $query = User::query()->orderBy('login_date', "DESC")->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('login_date', function($row){
                return Carbon::parse($row->login_date)->format('d F Y H:i:s');
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
