<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Teams;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller{
    public function index(){
        $data = $this->getSql()->get();

        $columns = [
            ['label' => 'Teams', 'field' => 'teams_name'],
            ['label' => 'Member', 'field' => 'member_name'],
            ['label' => 'Username', 'field' => 'username'],
            ['label' => 'Leader', 'field' => 'is_leader'],
        ];

        $selects = $this->getSelect();

        return view('pages.member.index', compact('data', 'columns', 'selects'));
    }

    public function data(){
        $query          = $this->getSql();
        $basePermission = permission();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('is_leader', function($row) {
                return $row->is_leader == 1 ? 'Yes': 'No';
            })
            ->addColumn('action', function ($row) use ($basePermission) {
                $buttons = '';

                if(in_array($basePermission.'.edit', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-warning btn-edit text-white" data-id="'.$row->member_id.'">
                        <i class="bi bi-pencil"></i>
                    </button>';
                }

                if(in_array($basePermission.'.delete', session('permission_security', []))){
                    $buttons .= '
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->member_id.'" data-name="'.$row->member_name.'">
                        <i class="bi bi-trash"></i>
                    </button>';
                }

                return $buttons;
            })
            ->filterColumn('teams_name', function($query, $keyword){
                $query->where('t.teams_name', 'like', "%$keyword%");
            })
            ->filterColumn('member_name', function($query, $keyword){
                $query->where('rep.repnm', 'like', "%$keyword%");
            })
            ->filterColumn('username', function($query, $keyword){
                $query->where('us.username', 'like', "%$keyword%");
            })
            ->filterColumn('is_leader', function($query, $keyword){
                $keyword = strtolower(trim($keyword));

                if (strpos('yes', $keyword) === 0) {
                    $query->where('is_leader', 1);
                } elseif (strpos('no', $keyword) === 0) {
                    $query->where('is_leader', 0);
                }
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(){
        return view('pages.member.add');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'teams_id'  => 'required|numeric|exists:t_teams,teams_id',
            'user_id' => 'required|numeric|exists:tbl_users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $validated['is_leader'] = $request->has('is_leader') ? 1: 0;

            $member = Member::create($validated);

            ActivityLogger::create([
                'subject_type'  => 'Member',
                'subject_id'    => $member->member_id,
                'new_values'    => $validated
            ]);

            return redirect()->route('web.member.index')->with('success', 'Member created successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create member: ' . $e->getMessage());
        }
    }

    public function edit($id){
        $data = $this->getSql()->where('t_member.member_id', $id)->first();

        return response()->json($data);
    }

    public function update(Request $request, $id){
        $data = Member::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'teams_id'  => 'required|numeric|exists:t_teams,teams_id',
            'user_id' => 'required|numeric|exists:tbl_users,id'
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

            $validated['is_leader'] = $request->has('is_leader') ? 1: 0;
            $data->update($validated);

            ActivityLogger::update([
                'subject_type'  => 'Member',
                'subject_id'    => $id,
                'new_values'    => $newValues,
                'old_values'    => $oldValues,
            ]);

            return redirect()->route('web.member.index')->with('success', 'Member updated successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update member: ' . $e->getMessage());
        }
    }

    public function destroy($id){
        $data = Member::where('member_id', $id)->first();

        try {
            $oldValues = $data->toArray();

            $data->update([
                'status'        => '0',
                'deleted_date'  => now(),
                'deleted_by'    => session('user_security')->id ?? 1
            ]);

            ActivityLogger::delete([
                'subject_type'  => 'Member',
                'subject_id'    => $id,
                'old_values'    => $oldValues
            ]);

            return redirect()->route('web.member.index')->with('success', 'Member deleted successfully!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete member: ' . $e->getMessage());
        }
    }

    private function getSql(){
        $sql = Member::leftJoin('t_teams as t', 't.teams_id', 't_member.teams_id')
                    ->leftJoin('tbl_users as us', 'us.id', '=', 't_member.user_id')
                    ->leftJoin('phidb.arsalesrep as rep', 'rep.id', '=', 'us.kode_sales')
                    ->select(
                        't_member.*',
                        't.teams_name',
                        't.teams_description',
                        DB::raw('COALESCE(rep.repnm, us.username) as member_name'),
                        'us.username',
                    );

        return $sql;
    }

    private function getSelect(){
        $teams = Teams::get();
        $users = User::leftJoin('phidb.arsalesrep as rep', 'rep.id', '=', 'tbl_users.kode_sales')
                    ->select(
                        'tbl_users.*',
                        DB::raw('COALESCE(rep.repnm, tbl_users.username) as member_name'),
                        'tbl_users.id as user_id'
                    )->get();

        $data = [
            "teams" => $teams,
            "users" => $users
        ];

        return $data;
    }
}
