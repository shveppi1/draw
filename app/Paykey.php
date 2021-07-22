<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paykey extends Model
{
    protected $table = 'paykeys';


    protected $fillable = [
        'key',
        'pay_id',
        'draw_id',
        'payer'
    ];
}