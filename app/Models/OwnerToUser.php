<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerToUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'user_id'
    ];

    function owner(){
        return $this->hasMany(User::class,);
    }

    function users(){
        return $this->hasMany(User::class,'id','user_id');
    }
}
