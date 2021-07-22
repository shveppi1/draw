<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Champion extends Model
{
    protected $table = 'champions';


    protected $fillable = [
        'user_id',
        'draw_id',
        'user_name',
        'first_name',
    ];
}