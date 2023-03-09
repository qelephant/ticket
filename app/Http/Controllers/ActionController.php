<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\ActionStatus;
use App\Models\User;
use App\Models\UserToActionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionController extends Controller
{

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'photo' => 'required',
            'quantity_presents' => 'required|integer',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ]);

        if(!Auth::user()->can('create action')){
            return response()->json([
                'error' => "Нou don't have access",
            ]);
        }

        $status = ActionStatus::query()
            ->where('name','stop')->first();

        $data['status_id'] = $status->id;
        $data['owner_id'] = Auth::user()->id;

        $action = Action::create($data);
        $action->save();

        return response()->json([
            'action' => $action,
        ]);
    }

    public function list(Request $request)
    {
        if(Auth::user()->can('create super-admin')){
            return response()->json([
                'actions' => Action::all(),
            ]);
        }

        if(Auth::user()->can('create manager')){
            return response()->json([
                'actions' => Auth::user()->actionsOwner,
            ]);
        }

        $rules = Auth::user()->actionRule;
        $usersToActions = [];
        foreach($rules as $rule) {
            array_push($usersToActions, $rule->action);
        }
        return response()->json([
            'actions' => $usersToActions,
        ]);
    }

    public function edit(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:App\Models\Action,id',
            'name' => 'required',
            'photo' => 'required',
            'quantity_presents' => 'required|integer',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ]);

        if(!Auth::user()->can('create super-admin')){
            if(!Auth::user()->actionsOwner->where('id','=',$data['id'])->count()>0){
                if(!Auth::user()->actionRule->where('action_id','=',$data['id'])->first()['edit_action']){
                    return response()->json([
                        'error' => "Нou don't have access",
                    ]);
                }
            }
        }

        $action = Action::find($data['id']);
        $action->fill($data);
        $action->save();
        return response()->json([
            'action' => $action,
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:App\Models\Action,id'
        ]);

        if(!Auth::user()->can('create super-admin')){
            if(!Auth::user()->actionsOwner->where('id','=',$data['id'])->count()>0){
                $rules = Auth::user()->actionRule->where('action_id','=',$data['id']);
                if(!$rules->count()>0 || !$rules->first()['edit_action']){
                    return response()->json([
                        'error' => "Нou don't have access",
                    ]);
                }
            }
        }

        Action::destroy($data['id']);
        UserToActionRule::where('action_id', $data['id'])->get()->delete();
        return response()->json([
            'success' => "Action destroyed",
        ]);
    }

    public function share(Request $request)
    {
        $data = $request->validate([
            'id' => 'exists:App\Models\UserToActionRule,id',
            'action_id' => 'required|exists:App\Models\Action,id',
            'user_id' => 'required|exists:App\Models\User,id',
            'view_presents' => 'required|boolean',
            'view_type_presents' => 'required|boolean',
            'edit_action' => 'required|boolean',
            'view_action' => 'required|boolean'
        ]);

        if(!Auth::user()->can('create super-admin')){
            if(!Auth::user()->actionsOwner->where('id','=',$data['action_id'])->count()>0){
                if(!Auth::user()->actionRule->where('action_id','=',$data['action_id'])->first()['edit_action']){
                    return response()->json([
                        'error' => "Нou don't have access",
                    ]);
                }
            }
        }

        if(!Auth::user()->hasRole('super-admin')){
            if(!Auth::user()->children()->firstWhere('id',$data['user_id'])){
                return response()->json([
                    'error' => "Нou don't have access",
                ]);
            }
        }

        if($data['id']){
            $share = UserToActionRule::find($data['id']);
            $share->fill($data);
        }else{
            $share = UserToActionRule::create($data);
        }
        $share->save();

        return response()->json([
            'share' => $share,
        ]);
    }

    public function statuses(Request $request)
    {
        return response()->json([
            'statuses' => ActionStatus::all(),
        ]);
    }
}
