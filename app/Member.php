<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';


    protected $fillable = [
        'draw_id',
        'user_id',
        'user_name',
        'first_name',
    ];
}
