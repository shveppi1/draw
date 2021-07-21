<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Canal extends Model
{
    protected $table = 'canals';


    protected $fillable = [
        'admin_id',
        'chat_id',
        'chat_title',
        'chat_username',
    ];
}