<?php

namespace App\Http\Controllers;






use App\Draw;
use App\Member;
use App\Paykey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Cookie;
use Illuminate\Support\Facades\Mail;
use Log;
use Illuminate\Support\Str;

class MainController extends Controller
{
    public function index() {

        $rand = Str::random(8);
        // все

        return view('pages.main', compact('rand'));
    }


    public function thanks(Request $request) {

        $cookie = $request->cookie('user');

        $paykey = null;
        if(isset($_COOKIE['user_pwd'])) {
            if(strlen($_COOKIE['user_pwd']) > 2) {
                $arPaykey = Paykey::where('pay_id', $_COOKIE['user_pwd'])->where('payer', '1')->first();
                if($arPaykey != null){
                    $paykey = $arPaykey->key;
                }
            }
        }


        return view('pages.thanks', compact('paykey'));
    }


    public function createKey(Request $request) {



        if($request->sum == '1400') {

            Paykey::create([
                'key' => rand(100000,999999),
                'pay_id' => $request->label
            ]);


            return 'true';
        } else {
            return 'false';
        }

    }


    public function payTrans(Request $request) {

        $req = $request->all();

        $logTxt = print_r($req, true);

        Log::channel('paylog')->info($logTxt);


        $paykey = Paykey::where('pay_id', $req['label'])->where('payer', '0')->first();

        if($paykey != null) {
            $paykey->payer = 1;

            $paykey->save();
            $value['promo'] = $paykey->key;
            $value['title'] = 'Ваш промокод публикации готов';


            Mail::to($req['email'])->send(new \App\Mail\WelcomeMail($value));

        }




    }





    public function test() {

/*
        $this_time = Carbon::parse('2021-07-22 15:00')->format('Y-m-d H:i:s');

        $aRdraws = Draw::where('date_finish', '<', $this_time)->where('published_at', '<>', '')->get();

        foreach($aRdraws as $draw) {
            $members = Member::where('draw_id', $draw->id)->inRandomOrder()->limit($draw->count_victory)->get();


            if($members->count()){

            }





        }*/


/*
        $str = 'https://t.me/kpkpadmin';


        $pos = strripos($str, 't.me');

        if($pos !== false) {

            $preg = preg_match('/t.me\/(.*)/', $str, $match);

        } else {

            echo 'Нету';

        }



        dd($match[1]);

        //dd($match);

*/
        return rand(100000,999999);
    }
}