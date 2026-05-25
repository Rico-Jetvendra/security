<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller{
    public function index(){
        $data = Application::all();

        $columns = [
            ['label' => 'Application', 'field' => 'application_name'],
            ['label' => 'Type', 'field' => 'application_type'],
        ];

        $selects = $this->getSelect();

        return view('pages.application.index', compact('data', 'columns', 'selects'));
    }

    public function data(){
        $query = Application::query();
        $basePermission = permission();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('application_type', function($row){
                $app_type = config('combobox.app_type');

                foreach($app_type as $type){
                    if($type['id'] == $row->application_type){
                        return $type['name'];
                    }
                }
            })
            ->addColumn('action', function ($row) use ($basePermission) {
                $buttons = '';

                if(in_array($basePermission.'.edit', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-warning btn-edit text-white" data-id="'.$row->application_id.'">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                if(in_array($basePermission.'.delete', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->application_id.'" data-name="'.$row->application_name.'">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                return $buttons;
            })
            ->filterColumn('application_name', function($query, $keyword){
                $query->where('application_name', 'like', "%$keyword%");
            })
            ->filterColumn('application_type', function($query, $keyword){
                $app_type = config('combobox.app_type');

                foreach($app_type as $type){
                    if(stripos($type['name'], $keyword) !== false){
                        $query->orWhere('application_type', $type['id']);
                    }
                }
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.application.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'application_name' => 'required|string|max:255|unique:t_application,application_name',
            'application_type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $application = Application::create($validated);

            ActivityLogger::create([
                'subject_type'  => 'Application',
                'subject_id'    => $application->application_id,
                'new_values'    => $validated
            ]);

            return redirect()->route('web.application.index')->with('success', 'Application created successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create application: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = Application::findOrFail($id);

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = Application::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'application_name' => 'required|string|max:255|unique:t_application,application_name',
            'application_type' => 'required|integer',
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
                'subject_type'  => 'Application',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.application.index')->with('success', 'Application updated successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update application: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = Application::findOrFail($id);

        try {
            $oldValues = $data->toArray();

            $data->update([
                'status'        => '0',
                'deleted_date'  => now(),
                'deleted_by'    => session('user_security')->id ?? 1
            ]);

            ActivityLogger::delete([
                'subject_type'  => 'Application',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.application.index')->with('success', 'Application deleted successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete application: ' . $e->getMessage());
        }
    }

    private function getSelect(){
        $type = config('combobox.app_type');
        $app_type = [];

        foreach($type as $item){
            $app_type[] = (object) [
                'application_type' => $item['id'],
                'application_type_name' => $item['name']
            ];
        }

        $data = [
            "app_type" => $app_type
        ];

        return $data;
    }
}
