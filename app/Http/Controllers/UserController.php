<?php


namespace App\Http\Controllers;
use App\Events\ChatMessage;
use App\Models\OwnerToUser;
use App\Models\User;
use App\Models\UserToActionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if (!Auth::attempt($data)) {
            return response()->json([
                'message' => 'You cannot sign with those credentials',
                'errors' => 'Unauthorised'
            ], 401);
        }

        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        $token = $user->createToken('ar-ticket')->accessToken;
        $token->token->expires_at = Carbon::now()->addMonth();
        $token->token->save();
        $success['token'] =  $token;
        $success['name'] =  $user->name;
        $success['role'] = $user->getRoleNames();
        return response()->json($success);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($data)) {
            return response()->json([
                'message' => 'You cannot sign with those credentials',
                'errors' => 'Unauthorised'
            ], 401);
        }

        $token = Auth::user()->createToken(config('app.name'));
        $token->token->expires_at = Carbon::now()->addMonth();

        $token->token->save();
        $success['token'] =  $token->accessToken;
        $success['name'] =  Auth::user()->name;
        $success['role'] = Auth::user()->getRoleNames();
        return response()->json($success);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'You are successfully logged out',
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'full_name' => 'required',
            'phone' => 'required|regex:/(7)[0-9]{9}/',
            'photo' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'roleId' => 'required|exists:Spatie\Permission\Models\Role,id'
        ]);

        $role = Role::findOrFail($data['roleId']);

        if(!Auth::user()->can('create super-admin')){
            if($role->name=="super-admin" || $role->name=="admin")
                return response()->json([
                    'error' => "Нou don't have access",
                ]);
        }

        if(!Auth::user()->can('create manager')){
            return response()->json([
                'error' => "Нou don't have access",
            ]);
        }

        if(User::query()->where('name',$data['name'])->first()){
            return response()->json([
                'error' => 'User ' . $data['name'] . ' exists',
            ]);
        }


        $data['password'] = bcrypt($data['password']);
        $data = array_diff_key($data,array_flip(['c_password','roleId']));
        $user = User::firstOrCreate($data);
        $user->assignRole($role);
        $user->save();

        OwnerToUser::create(['owner_id'=>Auth::user()->id,'user_id'=>$user->id]);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function list(Request $request){
        if(Auth::user()->can('create super-admin')){
            return response()->json([
                'users' => User::role(['super-admin','admin','manager'])->get(),
            ]);
        }

        if(Auth::user()->can('create manager')){
            return response()->json([
                'users' => Auth::user()->children(),
            ]);
        }

        return response()->json([
            'error' => "Нou don't have access",
        ]);
    }

    public function edit(Request $request){
        $data = $request->validate([
            'id' => 'required|exists:App\Models\User,id',
            'name' => 'required',
            'email' => 'required|email',
            'full_name' => 'required',
            'phone' => 'required|regex:/(7)[0-9]{9}/',
            'photo' => 'required',
            'roleId' => 'required|exists:Spatie\Permission\Models\Role,id'
        ]);

        $role = Role::find($data['roleId']);

        $data = array_diff_key($data,array_flip(['roleId']));

        $userExist = User::where([
                ['id','<>',$data['id']],
                ['name','=',$data['name']]
            ])->orWhere([
                ['id','<>',$data['id']],
                ['email','=',$data['email']]
            ])->orWhere([
                ['id','<>',$data['id']],
                ['phone','=',$data['phone']]
        ])->get();

        if($userExist->count()>0){
            return response()->json([
                'error' => "User with that name, email or phone is exist",
            ]);
        }
        $user = User::find($data['id']);

        if(!Auth::user()->hasAnyPermission(['create super-admin','create admin','create manager'])){
            return response()->json([
                'error' => "Нou don't have access",
            ]);
        }

        if($user->id!==Auth::user()->id) {
            if (!Auth::user()->hasAnyPermission(['create super-admin','create admin']) && $role->name != "manager") {
                return response()->json([
                    'error' => "Нou don't have access",
                ]);
            }
            if(!Auth::user()->hasAnyPermission(['create super-admin','create admin']) && $user->hasAnyPermission(['create manager'])){
                return response()->json([
                    'error' => "Нou don't have access",
                ]);
            }
            if(!Auth::user()->hasRole('super-admin')){
                if(!Auth::user()->children()->firstWhere('id',$user->id)){
                    return response()->json([
                        'error' => "Нou don't have access",
                    ]);
                }
            }
            $user->assignRole($role);
        }

        $user->fill($data);
        $user->update();

        return response()->json([
            'user' => $user,
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:App\Models\User,id'
            ]);

        if(!Auth::user()->hasAnyPermission(['create super-admin','create admin','create manager'])){
            return response()->json([
                'error' => "Нou don't have access",
            ]);
        }

        if(!Auth::user()->hasRole('super-admin')){
            if(!Auth::user()->children()->firstWhere('id',$data['id'])){
                return response()->json([
                    'error' => "Нou don't have access",
                ]);
            }
        }

        User::destroy($data['id']);

        return response()->json([
            'success' => "User destroyed",
        ]);
    }

    public function checkRoomAccess(Request $request)
    {
        if($request['channel_name']==="private-laravel_database_chat"){
            logger($request->user());
        }
        return response()->json([
            'message' => 'You are successfully logged out',
        ]);
    }

    public function testSocket(Request $request)
    {
        event(new ChatMessage("hello"));
        return response()->json([
            'message' => 'Socket send',
        ]);
    }

}
