<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participants';


    protected $fillable = [
        'member_id',
        'chat_id',
        'part_id',
        'user_name',
        'first_name',
        'left_chat',
    ];
}
