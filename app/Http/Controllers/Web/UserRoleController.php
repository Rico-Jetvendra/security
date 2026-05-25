<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Services\ActivityLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

use Yajra\DataTables\Facades\DataTables;

class UserRoleController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'User', 'field' => 'user_name'],
            ['label' => 'Role', 'field' => 'role_name'],
        ];

        $selects = $this->getSelect();

        return view('pages.user_role.index', compact('data', 'columns', 'selects'));
    }

    public function data(){
        $query = $this->getSql();
        $basePermission = permission();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($basePermission) {
                $buttons = '';

                if(in_array($basePermission.'.edit', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-warning btn-edit text-white" data-id="'.$row->user_roles_id.'">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                if(in_array($basePermission.'.delete', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->user_roles_id.'" data-name="'.$row->user_name.'">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                return $buttons;
            })
            ->filterColumn('user_name', function($query, $keyword){
                $query->where('us.username', 'like', "%$keyword%");
            })
            ->filterColumn('role_name', function($query, $keyword){
                $query->where('r.name', 'like', "%$keyword%");
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.user_role.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'integer',
                'exists:tbl_users,id',
                Rule::unique('t_user_roles')->where(function ($query) use ($request) {
                    return $query->where('role_id', $request->role_id);
                }),
            ],
            'role_id' => 'required|integer|exists:t_roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $user = UserRole::create($validated);
            ActivityLogger::create([
                'subject_type'  => 'User Role',
                'subject_id'    => $user->id,
                'new_values'    => $validated
            ]);

            return redirect()->route('web.user-role.index')->with('success', 'User Role created successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create user role: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSql()->where('t_user_roles.id', '=', $id)->firstOrFail();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = UserRole::find($id);

        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'integer',
                'exists:tbl_users,id',
                Rule::unique('t_user_roles', 'user_id')->ignore($id),
            ],
            'role_id' => 'required|integer|exists:t_roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

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
                'subject_type'  => 'User Role',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.user-role.index')->with('success', 'User Role updated successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update user role: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = UserRole::find($id);

        try {
            $oldValues = $data->toArray();

            $data->delete();

            ActivityLogger::delete([
                'subject_type'  => 'User Role',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.user-role.index')->with('success', 'User Role deleted successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete user role: ' . $e->getMessage());
        }
    }

    private function getSql(){
        $sql = UserRole::join('t_roles as r', 't_user_roles.role_id', 'r.id')
                        ->join('tbl_users as us', 't_user_roles.user_id', 'us.id')
                        ->select(
                            't_user_roles.id as user_roles_id',
                            'r.id as role_id',
                            'r.name as role_name',
                            'us.id as user_id',
                            'us.username as user_name',
                        );

        return $sql;
    }

    private function getSelect(){
        $role = $this->changeSelect('role', Role::all());
        $user = $this->changeSelect('user', User::all());

        $data = [
            "role" => $role,
            "user" => $user
        ];

        return $data;
    }

    private function changeSelect($name, $data){
        $selects = [];

        foreach($data as $item){
            $selects[] = (object) [
                $name.'_id' => $item['id'],
                $name.'_name' => $item['name'] ?? $item['username']
            ];
        }

        return $selects;
    }
}
