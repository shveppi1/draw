<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Draw extends Model
{
    protected $table = 'draws';


    protected $fillable = [
        'admin_id',
        'chat_id',
        'chat_title',
        'message_id',
        'edit_message_id',
        'text',
        'text_btn',
        'new_part',
        'count_part',
        'count_victory',
        'pay_key',
        'public',
        'status',
        'date_finish',
        'published_at',
    ];
}
