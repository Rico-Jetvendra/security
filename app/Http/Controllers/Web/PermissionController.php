<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Permission;
use App\Services\ActivityLogger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'Application', 'field' => 'application_name'],
            ['label' => 'Permission', 'field' => 'name'],
        ];

        $selects = $this->getSelect();

        return view('pages.permission.index', compact('data', 'columns', 'selects'));
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
        return view('pages.permission.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'application_id' => 'required|integer|exists:t_application,application_id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated  = $validator->validated();
        $default    = $request->has('default');

        try {
            $permission = Permission::create($validated);

            ActivityLogger::create([
                'subject_type'  => 'Permission',
                'subject_id'    => $permission->id,
                'new_values'    => $validated
            ]);

            if($default){
                $defaultButton = ['add', 'edit', 'delete'];

                foreach($defaultButton as $button){
                    $perm = Permission::create([
                        'application_id'    => $validated['application_id'],
                        'name'              => $validated['name'].'.'.$button,
                        'description'       => 'Default permission for '.$button.' button of '.$validated['name']
                    ]);

                    ActivityLogger::create([
                        'subject_type'  => 'Permission',
                        'subject_id'    => $perm->id,
                        'new_values'    => $validated
                    ]);
                }
            }

            return redirect()->route('web.permission.index')->with('success', 'Permission created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSql()->where('t_permissions.id', $id)->firstOrFail();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = $this->getSql()->where('t_permissions.id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
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
                'subject_type'  => 'Permission',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.permission.index')->with('success', 'Permission updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update permission: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = Permission::findOrFail($id);

        try {
            $oldValues = $data->toArray();

            $data->update([
                'status'        => '0',
                'deleted_date'  => now(),
                'deleted_by'    => session('user_security')->id ?? 1
            ]);

            ActivityLogger::delete([
                'subject_type'  => 'Permission',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.permission.index')->with('success', 'Permission deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }

    private function getSql(){
        $sql = Permission::join('t_application as app', 't_permissions.application_id', 'app.application_id')
            ->select('t_permissions.*', 'app.application_name');

        return $sql;
    }

    private function getSelect(){
        $applications = Application::all();

        return [
            'application' => $applications,
        ];
    }
}
