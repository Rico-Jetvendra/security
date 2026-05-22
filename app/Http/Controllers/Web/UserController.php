<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Application;
use App\Models\User;

use App\Services\ActivityLogger;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'Application', 'field' => 'application_name'],
            ['label' => 'User', 'field' => 'username'],
            ['label' => 'Email', 'field' => 'email'],
        ];

        $selects = $this->getSelect();

        return view('pages.user.index', compact('data', 'columns', 'selects'));
    }

    public function data(){
        $query          = $this->getSql();
        $basePermission = permission();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($basePermission) {
                $buttons = '';

                if(in_array($basePermission.'.edit', session('permission', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-warning btn-edit text-white" data-id="'.$row->id.'">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                if(in_array($basePermission.'.delete', session('permission', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'" data-name="'.$row->name.'">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                return $buttons;
            })
            ->filterColumn('username', function($query, $keyword) {
                $query->where('tbl_users.username', 'like', "%{$keyword}%");
            })
            ->filterColumn('email', function($query, $keyword) {
                $query->where('tbl_users.email', 'like', "%{$keyword}%");
            })
            ->filterColumn('application_name', function($query, $keyword) {
                $query->where('app.application_name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.user.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'application_id'    => 'required|integer|exists:t_application,application_id',
            'username'          => 'required|string|max:255|unique:tbl_users,username',
            'password'          => 'required|string|confirmed|min:6',
            'email'             => 'required|email',
            'kode_sales'        => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $user = User::create($validated);

            ActivityLogger::create([
                'subject_type'  => 'User',
                'subject_id'    => $user->id,
                'new_values'    => $validated
            ]);

            return redirect()->route('web.user.index')->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSql()->where('tbl_users.id', $id)->firstOrFail();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = $this->getSql()->where('tbl_users.id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'application_id'    => 'required|integer|exists:t_application,application_id',
            'username'          => 'required|string|max:255|unique:tbl_users,username,'.$id,
            'password'          => 'required|string|confirmed|min:6',
            'email'             => 'required|email',
            'kode_sales'        => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        if (empty($validated['password']) || $validated['password'] === $data['password']) {
            unset($validated['password']);
        }

        try {
            $oldValues = [];
            $newValues = [];

            foreach ($validated as $field => $value) {
                if ($data->$field != $value) {
                    $oldValues[$field] = $data->$field;
                    $newValues[$field] = $value;
                }
            }

            $data->update($validated);

            ActivityLogger::update([
                'subject_type'  => 'User',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.user.index')->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = User::findOrFail($id);

        try {
            $oldValues = $data->toArray();

            $data->update([
                'status'        => '0',
                'deleted_date'  => now(),
                'deleted_by'    => session('user_security')->id ?? 1
            ]);

            ActivityLogger::delete([
                'subject_type'  => 'User',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.user.index')->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    private function getSql(){
        $sql = User::join('t_application as app', 'tbl_users.application_id', 'app.application_id')
                    ->leftJoin('phidb.arsalesrep as ar', 'tbl_users.kode_sales', 'ar.id')
                    ->select(
                        'tbl_users.*',
                        'tbl_users.password as password_confirmation',
                        'app.application_name',
                        'ar.repnm as sales_name',
                    );

        return $sql;
    }

    private function getSelect(){
        $applications = Application::all();
        $kode         = DB::table('phidb.arsalesrep')->where('active_', '1')->get();
        $sales        = [];

        foreach($kode as $item){
            $sales[] = (object) [
                'kode_sales' => $item->id,
                'sales_name' => $item->repnm
            ];
        }

        return [
            'application' => $applications,
            'kode_sales'  => $sales,
        ];
    }
}
