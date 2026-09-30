<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Teams;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TeamsController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'Teams', 'field' => 'teams_name'],
            ['label' => 'Parent', 'field' => 'parent_name'],
        ];

        $selects = $this->getSelect();

        return view('pages.teams.index', compact('data', 'columns', 'selects'));
    }

    public function data(){
        $query          = $this->getSql();
        $basePermission = permission();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('teams_type', function($row){
                $app_type = config('combobox.app_type');

                foreach($app_type as $type){
                    if($type['id'] == $row->teams_type){
                        return $type['name'];
                    }
                }
            })
            ->addColumn('action', function ($row) use ($basePermission) {
                $buttons = '';

                if(in_array($basePermission.'.edit', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-warning btn-edit text-white" data-id="'.$row->teams_id.'">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                if(in_array($basePermission.'.delete', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->teams_id.'" data-name="'.$row->teams_name.'">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                return $buttons;
            })
            ->filterColumn('teams_name', function($query, $keyword){
                $query->where('teams_name', 'like', "%$keyword%");
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.teams.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'teams_name'        => 'required|string|max:255|unique:t_teams,teams_name',
            'parent_id'      => 'nullable',
            'teams_description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $validated['teams_parent'] = $validated['parent_id'];

            $teams = Teams::create($validated);

            ActivityLogger::create([
                'subject_type'  => 'Teams',
                'subject_id'    => $teams->teams_id,
                'new_values'    => $validated
            ]);

            return redirect()->route('web.teams.index')->with('success', 'Teams created successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create teams: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSql()->where('t_teams.teams_id', $id)->first();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = Teams::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'teams_name'        => 'required|string|max:255|unique:t_teams,teams_name',
            'parent_id'      => 'nullable',
            'teams_description' => 'nullable',
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

            $validated['teams_parent'] = $validated['parent_id'];
            $data->update($validated);

            ActivityLogger::update([
                'subject_type'  => 'Teams',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.teams.index')->with('success', 'Teams updated successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update teams: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = Teams::findOrFail($id);

        try {
            $oldValues = $data->toArray();

            $data->update([
                'status'        => '0',
                'deleted_date'  => now(),
                'deleted_by'    => session('user_security')->id ?? 1
            ]);

            ActivityLogger::delete([
                'subject_type'  => 'Teams',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.teams.index')->with('success', 'Teams deleted successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete teams: ' . $e->getMessage());
        }
    }

    private function getSql(){
        $sql = Teams::leftJoin('t_teams as t', 't.teams_id', 't_teams.teams_parent')
                    ->select(
                        't_teams.*',
                        't.teams_id as parent_id',
                        't.teams_name as parent_name'
                    );

        return $sql;
    }

    private function getSelect(){
        $parents = Teams::select('t_teams.teams_id as parent_id', 't_teams.teams_name as parent_name')->get();

        $data = [
            "parents" => $parents
        ];

        return $data;
    }
}
