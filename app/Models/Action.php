<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'owner_id',
        'name',
        'photo',
        'start_at',
        'end_at',
        'quantity_presents'
    ];

    function rules(){
        return $this->hasMany(UserToActionRule::class);
    }

    function owner(){
        return $this->belongsTo(User::class);
    }
}
