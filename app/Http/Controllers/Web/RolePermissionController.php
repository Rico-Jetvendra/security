<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Application;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;

use App\Services\ActivityLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Yajra\DataTables\Facades\DataTables;

class RolePermissionController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'Application', 'field' => 'application_name'],
            ['label' => 'Role', 'field' => 'name'],
        ];

        $selects = $this->getSelect();

        return view('pages.role_permission.index', compact('data', 'columns', 'selects'));
    }

    public function data(){
        $query = $this->getSql();
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
            ->filterColumn('application_name', function($query, $keyword) {
                $query->where('app.application_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('role_name', function($query, $keyword) {
                $query->where('r.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('permission_name', function($query, $keyword) {
                $query->where('p.name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.role_permission.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'role_id'           => 'required|integer|exists:t_roles,id',
            'application_id'    => 'required|integer|exists:t_application,application_id',

            'permissions'       => 'required|array',
            'permissions.*'     => 'required|integer|exists:t_permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        DB::beginTransaction();

        try {
            $permissions = array_unique($validated['permissions']);
            $insertData  = [];

            foreach ($permissions as $permissionId) {
                $insertData[] = [
                    'application_id' => $validated['application_id'],
                    'role_id'        => $validated['role_id'],
                    'permission_id'  => $permissionId,
                ];
            }
            RolePermission::insert($insertData);

            ActivityLogger::create([
                'subject_type'  => 'Role Permission',
                'subject_id'    => $validated['role_id'],

                'description'   => 'Assigned permissions to role.',

                'new_values'    => [
                    'role_id'           => $validated['role_id'],
                    'permissions'       => $permissions,
                    'total_permissions' => count($permissions)
                ]
            ]);
            DB::commit();

            return redirect()->route('web.role-permission.index')->with('success', 'Role Permission created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create role-permission: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSqlDetail()->where('r.id', $id)->get();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'role_id'           => 'required|integer|exists:t_roles,id',
            'application_id'    => 'required|integer|exists:t_application,application_id',

            'permissions'       => 'required|array',
            'permissions.*'     => 'required|integer|exists:t_permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        DB::beginTransaction();

        try {
            $permissions    = array_unique($validated['permissions']);
            $oldPermissions = RolePermission::where('role_id', $validated['role_id'])->pluck('permission_id')->toArray();

            RolePermission::where('role_id', $validated['role_id'])->delete();

            $insertData = [];

            foreach ($permissions as $permissionId) {
                $insertData[] = [
                    'application_id' => $validated['application_id'],
                    'role_id'        => $validated['role_id'],
                    'permission_id'  => $permissionId,
                ];
            }
            RolePermission::insert($insertData);

            $addedPermissions       = array_diff($permissions, $oldPermissions);
            $removedPermissions     = array_diff($oldPermissions, $permissions);

            $oldPermissionNames     = Permission::whereIn('id', $oldPermissions)->pluck('name')->toArray();
            $newPermissionNames     = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
            $addedPermissionNames   = Permission::whereIn('id', $addedPermissions)->pluck('name')->toArray();
            $removedPermissionNames = Permission::whereIn('id', $removedPermissions)->pluck('name')->toArray();

            DB::commit();

            if (!empty($addedPermissionNames) || !empty($removedPermissionNames)) {
                ActivityLogger::update([
                    'subject_type'      => 'Role Permission',
                    'subject_id'        => $validated['role_id'],
                    'description'       => 'Updated role permissions.',
                    'old_values' => [
                        'permissions'   => $oldPermissionNames
                    ],
                    'new_values' => [
                        'permissions'           => $newPermissionNames,
                        'added_permissions'     => $addedPermissionNames,
                        'removed_permissions'   => $removedPermissionNames
                    ]
                ]);
            }

            $this->relog($validated['role_id']);

            return redirect()->route('web.role-permission.index')->with('success', 'Role Permission updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to update role-permission: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        try {
            $oldPermissions     = RolePermission::where('role_id', $id)->pluck('permission_id')->toArray();
            $oldPermissionNames = Permission::whereIn('id', $oldPermissions)->pluck('name')->toArray();

            RolePermission::where('role_id', $id)->delete();

            if (!empty($oldPermissionNames)) {
                ActivityLogger::delete([
                    'subject_type'  => 'Role Permission',
                    'subject_id'    => $id,
                    'description'   => 'Deleted role permissions.',

                    'old_values' => [
                        'permissions'       => $oldPermissionNames,
                        'total_permissions' => count($oldPermissionNames)
                    ]
                ]);
            }

            return redirect()->route('web.role-permission.index')->with('success', 'Role Permission deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete role-permission: ' . $e->getMessage());
        }
    }

    public function getRolePermission(Request $request){
        $roles = Role::where('application_id', $request->application_id)->get();
        $permissions = Permission::where('application_id', $request->application_id)->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    private function getSql(){
        $sql = Role::join('t_application as app', 't_roles.application_id', 'app.application_id')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('t_role_permissions as rp')
                            ->whereColumn('rp.role_id', 't_roles.id');
                    })
                    ->select(
                        't_roles.*',
                        'app.application_name'
                    );

        return $sql;
    }

    private function getSqlDetail(){
        $sql = RolePermission::join('t_application as app', 't_role_permissions.application_id', 'app.application_id')
                            ->join('t_roles as r', 't_role_permissions.role_id', 'r.id')
                            ->join('t_permissions as p', 't_role_permissions.permission_id', 'p.id')
                            ->select(
                                't_role_permissions.*',
                                'app.application_id',
                                'app.application_name',
                                'app.application_type',
                                'r.id as role_id',
                                'r.name as role_name',
                                'p.id as permission_id',
                                'p.name as permission_name',
                            );

        return $sql;
    }

    private function getSelect(){
        $applications   = Application::all();
        $roles          = $this->changeSelect('role', Role::all());
        $permissions    = $this->changeSelect('permission', Permission::all());

        return [
            'application' => $applications,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    private function changeSelect($name, $data){
        $select = [];

        foreach ($data as $key => $item) {
            $select[$key] = (object) [
                $name . '_id'   => $item->id,
                $name . '_name' => $item->name,
            ];
        }

        return $select;
    }

    private function relog($roleId){
        $user = session('user_security');

        if (!$user) {
            return;
        }

        $hasRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->where('status', '1')
            ->exists();

        if (!$hasRole) {
            return;
        }

        $permissions = DB::table('security.t_user_roles as ur')
            ->join(
                'security.t_role_permissions as rp',
                'rp.role_id',
                '=',
                'ur.role_id'
            )
            ->join(
                'security.t_permissions as p',
                'p.id',
                '=',
                'rp.permission_id'
            )
            ->where('ur.user_id', $user->id)
            ->where('ur.status', '=', '1')
            ->where('p.status', '=', '1')
            ->where('rp.status', '=', '1')
            ->pluck('p.name')
            ->toArray();

        $user = User::find($user->id);

        session([
            'user_security' => $user,
            'permission'    => $permissions
        ]);
    }
}
