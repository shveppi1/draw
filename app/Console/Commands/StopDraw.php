<?php

namespace App\Console\Commands;

use App\Champion;
use App\Draw;
use App\Member;
use App\Paykey;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class StopDraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stop-draw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop draw';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        Paykey::where('created_at','<', Carbon::now()->subDays(20))->delete();


        /*
        $aRdraws = Draw::where('date_finish', '<', date('Y-m-d H:i:s'))
            ->where('published_at', '<>', '')
            ->where('status', 'Опубликован')
            ->get();
        */

        $aRdraws = Draw::where('date_finish', '<', date('Y-m-d H:i:s'))
            ->where('published_at', '<>', '')
            ->where('status', 'Опубликован')
            ->orWhere(function ($query) {
                $query->where( 'date_finish', '<', date('Y-m-d H:i:s') )
                    ->where( 'published_at', '<>', '' )
                    ->where( 'public', 0 );
            })
            ->get();


        $log_arr = array();


        if($aRdraws->count()) {



            foreach ($aRdraws as $draw) {



                $members = Member::where('draw_id', $draw->id)->inRandomOrder()->limit($draw->count_victory)->get();






                if ($members->count()) {

                    $victory_send = '';

                    // Собираем и сохраняем победителей
                    foreach ($members as $member) {

                        if( Champion::where('draw_id', $member->draw_id)->where('user_id', $member->user_id)->doesntExist() ) {

                            Champion::create([
                                'user_id' => $member->user_id,
                                'draw_id' => $member->draw_id,
                                'user_name' => $member->user_name,
                                'first_name' => $member->first_name,
                            ]);

                        }

                        $user_name = (isset($member->user_name)) ? $member->user_name : $member->first_name;

                        $victory_send .= '<a href="tg://user?id='.$member->user_id.'">'.$user_name.'</a>'. PHP_EOL;

                            unset($user_name);
                    }





                } else {
                    $victory_send = 'Победителей нет. Ни одного участника не было';
                }



                // Отправляем сообщение

                $text_send = '<b>'.$draw->text.'</b>'.PHP_EOL;
                $text_send .= 'Завершен! Поздравляем победителей:'.PHP_EOL;
                $text_send .= $victory_send;


                $arrSend = array(
                    'chat_id' => $draw->chat_id,
                    'text' => $text_send,
                    'message_id' => $draw->message_id,
                    'parse_mode' => 'HTML',
                );



                try {
                    $public = Telegram::editMessageText($arrSend);
                }
                catch (Telegram\Bot\Exceptions\TelegramResponseException $err) {

                    $err_desc = $err->getResponseData();




                    if (strpos($err_desc['description'], 'MESSAGE_ID_INVALID') !== true) {


                        $draw->public = 1;


                        $notPublicMessage = true; // нет сообщения в группе
                    }


                }





                if(isset($public)){

                    $draw->public = 1;

                }



                if($draw->status != 'Завершен') {

                    $replace_id = str_replace('-100', '', $draw->chat_id);

                    $url_message = 'https://t.me/c/' . $replace_id . '/' .$draw->message_id;

                    $keyboard = Keyboard::make()
                        ->inline()
                        ->row(
                            Keyboard::inlineButton(
                                [
                                    'text' => 'Перейти к сообщению',
                                    'url' => $url_message,
                                ]
                            )

                        );


                    $reply_markup = $keyboard;


                    $text_adm = 'Ваш розыгрыш:'.PHP_EOL;
                    $text_adm .= $text_send;

                    $arrSendAdmin = array(
                        'chat_id' => $draw->admin_id,
                        'text' => $text_adm,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $reply_markup
                    );






                    try {
                        $adm_pub = Telegram::sendMessage($arrSendAdmin);
                    }
                    catch (Telegram\Bot\Exceptions\TelegramResponseException $err) {

                        $err_desc = $err->getResponseData();




                        if (strpos($err_desc['description'], 'bot was blocked by the user') !== true) {



                        }else {

                            $logTxt = print_r($err->getResponse(), true);

                            Log::channel('error_try')->info($logTxt);

                        }


                    }

                }





                $draw->status = 'Завершен';

                $draw->save();




                $log_arr[$draw->id]['draw_id'] = $draw->id;
                $log_arr[$draw->id]['victory'] = $victory_send;




            }

        }




        $logTxt = print_r($log_arr, true);

        Log::channel('drawstop')->info($logTxt);
    }
}
