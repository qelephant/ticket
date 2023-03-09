<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles,HasApiTokens,HasFactory,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'full_name',
        'photo',
        'phone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function actionsOwner(){
        return $this->hasMany(Action::class,'owner_id');
    }

    public function actionRule(){
        return $this->hasMany(UserToActionRule::class);
    }

    public function children(){
        $children = OwnerToUser::where('owner_id',$this->id)->get();
        $usersToActions = [];
        foreach ($children as $child){
            foreach ($child->users as $user)
                array_push($usersToActions, $user);
        }
        return collect($usersToActions);
    }
}
