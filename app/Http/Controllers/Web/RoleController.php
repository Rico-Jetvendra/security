<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Role;
use App\Services\ActivityLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'Application', 'field' => 'application_name'],
            ['label' => 'Role', 'field' => 'name'],
        ];

        $selects = $this->getSelect();

        return view('pages.role.index', compact('data', 'columns', 'selects'));
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
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.role.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name'              => [
                'required',
                'string',
                'max:255',

                Rule::unique('t_roles', 'name')->where(function ($query) {
                    return $query->where('status', '!=', 0);
                })
            ],
            'application_id'    => 'required|integer|exists:t_application,application_id',
            'description'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $role = Role::create($validated);

            ActivityLogger::create([
                'subject_type'  => 'Role',
                'subject_id'    => $role->id,
                'new_values'    => $validated
            ]);
            return redirect()->route('web.role.index')->with('success', 'Role created successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSql()->where('t_roles.id', $id)->firstOrFail();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = $this->getSql()->where('t_roles.id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:t_roles,name',
            'application_id' => 'required|integer|exists:t_application,application_id',
            'description' => 'nullable|string',
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
                'subject_type'  => 'Role',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.role.index')->with('success', 'Role updated successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = Role::findOrFail($id);

        try {
            $oldValues = $data->toArray();

            $data->update([
                'status'        => '0',
                'deleted_date'  => now(),
                'deleted_by'    => session('user_security')->id ?? 1
            ]);

            ActivityLogger::delete([
                'subject_type'  => 'Role',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.role.index')->with('success', 'Role deleted successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    private function getSql(){
        $sql = Role::join('t_application as app', 't_roles.application_id', 'app.application_id')
            ->select('t_roles.*', 'app.application_name');

        return $sql;
    }

    private function getSelect(){
        $applications = Application::all();

        return [
            'application' => $applications,
        ];
    }
}
