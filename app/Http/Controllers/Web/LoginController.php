<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller{
    public function index(){
        $userCount          = User::count();
        $applicationCount   = Application::count();
        $roleCount          = Role::count();
        $permissionCount    = Permission::count();

        $count = [
            "user"          => $userCount,
            "application"   => $applicationCount,
            "role"          => $roleCount,
            "permission"    => $permissionCount,
        ];

        return view('index', compact('count'));
    }

    public function signin(){
        return view('login');
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $user = User::where('application_id', '=', 3)->where('email', '=', $validated['email'])->first();
        if(!$user){
            ActivityLogger::failedLogin(['email' => $validated['email'], 'description' => 'User not found.']);
            return redirect()->route('web.index')->with('error', 'User not found!');
        }

        $passwordValid = false;
        if(Hash::check($validated['password'], $user->password)){
            $passwordValid = true;
        }else if($user->password === MD5($validated['password'])){
            $passwordValid = true;
            $user->update([
                'password' => $validated['password']
            ]);
        }

        if (!$passwordValid) {
            ActivityLogger::failedLogin(['email' => $validated['email'], 'description' => 'Invalid password!.']);
            return redirect()->back()->with('error', 'Invalid password!');
        }

        $permissions = DB::table('security.t_user_roles as ur')
                            ->join('security.t_role_permissions as rp', 'rp.role_id', '=', 'ur.role_id')
                            ->join('security.t_permissions as p', 'p.id', '=', 'rp.permission_id')
                            ->where('ur.user_id', $user->id)
                            ->where('ur.status', '=', '1')
                            ->where('p.status', '=', '1')
                            ->where('rp.status', '=', '1')
                            ->pluck('p.name')
                            ->toArray();

        $user->update(['login_date' => Carbon::now()]);
        session(['user_security' => $user, 'permission' => $permissions]);

        // Prevent session fixation
        $request->session()->regenerate();

        ActivityLogger::login();

        return redirect()->route('web.index')->with('success', 'Login Berhasil!');
    }

    public function logout(Request $request){
        ActivityLogger::logout();

        $request->session()->forget(['user_security', 'permission']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web.signin')->with('success', 'Berhasil logout!');
    }

    public function saveWebTokenSession(Request $request){
        $user = User::where('id', session('user_security')->id)->first();
        $user->update(['device_token' => $request->token]);

        $request->session()->put('webpush_initialized', true);

        return response()->json([
            'success' => true,
            'message' => 'Token saved in session',
        ]);
    }
}

?>
