<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToActionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_id',
        'view_presents',
        'view_type_presents',
        'edit_action',
        'view_action',
    ];

    function action(){
        return $this->belongsTo(Action::class);
    }

    function user(){
        return $this->belongsTo(User::class);
    }
}
