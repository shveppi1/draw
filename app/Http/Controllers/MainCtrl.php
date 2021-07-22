<?php

namespace App\Http\Controllers;


use App\Admin;
use App\Canal;
use App\Draw;
use App\Participant;
use App\Paykey;
use App\State;
use Carbon\Carbon;
use Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use App\Library\My as Shvp;
use Log;
use Illuminate\Support\Str;


class MainCtrl extends Controller
{


    public $keyboardHello = array(
        ['Создать розыгрыш', 'Мои розыгрыши'],
        ['Управление каналами'],
    );

    /*
    public $keyboardHello = array(
        ['Создать розыгрыш', 'Управление каналами'],
    );

    */

	
    public function update() {


        $updates = Telegram::getWebhookUpdates();



        if($updates->message && !$updates->callback_query) {
            $message = $updates->message;
        }



        $logTxt = print_r($updates, true);

        Log::channel('tg')->info($logTxt);




        /*

        Telegram::sendMessage([
            'chat_id' => $message['chat']['id'],
            'text' => 'Устраивайте розыгрыши'
            ]
        );

        */


        if($updates->callback_query) {

            $callback = $updates->callback_query;

            $message = $callback['message'];
            $user_id = $message['chat']['id'];

            if($message['chat']['type'] == 'private') {


                $prev_up = Shvp::getPrevMessage($user_id);


                $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
                preg_match('/\(.*?\)/', $res_sub, $action);
                $action = $action[0];



                switch($action) {
                    case '(editDraw)':

                        if(isset($prev_up['option']['draw_id']))
                            $draw_id = $prev_up['option']['draw_id'];


                        if(isset($draw_id) && $prev_up['option']['state'] == 'createdDraw' || $prev_up['option']['state'] == 'editDraw' ){

                            Shvp::editDraw($prev_up, $callback);

                        }

                        break;



                    case '(editDrawAddCanal)':

                        if(isset($prev_up['option']['draw_id']))
                            $draw_id = $prev_up['option']['draw_id'];


                        if(isset($draw_id)){

                            Shvp::editDrawCanal($prev_up, $callback);

                        }





                        break;



                    case '(editMyDraw)':


                        $message = $callback['message'];
                        $user_id = $message['chat']['id'];

                        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
                        preg_match('/\[(.*?)\]/', $res_sub, $method);

                        $draw_id = $method['1'];

                        $draw = Draw::where('id', $draw_id)->first();

                        $message_new_id = Shvp::backDraw($draw, $user_id, $draw->text);

                        $draw->edit_message_id = $message_new_id['message_id'];
                        $draw->save();

                        $new_state['state'] = 'editDraw';
                        $new_state['draw_id'] = $draw->id;


                        Shvp::saveState($user_id, $new_state);



                        break;





                    default:
                        $param = [
                            'callback_query_id' => $callback['id'],
                            'cache_time' => 0,
                            'text' => 'Не понимаю команду',
                            'show_alert' => true
                        ];

                        Telegram::answerCallbackQuery($param);



                }










            } else {


                $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
                preg_match('/\(.*?\)/', $res_sub, $action);
                $action = $action[0];



                switch($action) {

                    case '(vote)':


                        Shvp::addMembers($callback, $res_sub);


                        break;
                }



            }






            unset($message);
        }






        if($updates->message && $message['chat']['type'] == 'private'){



            $user_id = $message['from']['id'];
            $user_name = ( isset($message['from']['username']) ) ? '@' . $message['from']['username'] : $message['from']['first_name'];
            $first_name = $message['from']['first_name'];
            $text = (isset($message['text'])) ? $message['text'] : '';
            $prev_message = '';
            $prev_state = '';


            //$new_state = $updates;








            if(isset($message['from']['id'])){


                // Создание админа и сохранение текущего присланного сообщения
                if (Admin::where('user_id', '=', $message['from']['id'])->doesntExist()) {



                    Admin::create([
                        'user_id' => $user_id,
                        'user_name' => $user_name,
                        'first_name' => $first_name
                    ]);

                    $new_admin = true;

                } else {
                    $new_admin = false;
                    $prev_up = Shvp::getPrevMessage($user_id);
                    if(isset($prev_up['response']['message']))
                        $prev_message = $prev_up['response']['message'];
                    if(isset($prev_up['option']['state']))
                        $prev_state = $prev_up['option']['state'];
                    if(isset($prev_up['option']['draw_id']))
                        $prev_draw_id = $prev_up['option']['draw_id'];
                }


                Shvp::saveNewMessage($user_id, $updates);


            }



            if(!$new_admin) {
                // Если повторный запрос то тормозим
                if ($prev_message['date'] == $message['date']) {
                    return '';
                }
            }






            //Определение состояния
            $tg_state = Shvp::getStep($message, $prev_state);






            switch($tg_state) {



                case 'mainMenu': // Главное меню


                    $keyboard = $this->keyboardHello;

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);

                    Telegram::sendMessage([
                        'chat_id' => $message['chat']['id'],
                        'text' => 'Устраивайте розыгрыши призов (телефон, скидочный копон и т.д) на своем канале за подписку, и приглашение участников в группу.' . PHP_EOL . 'Я помогу Вам развить канал' . PHP_EOL . 'Сайт бота https://voterpro.ru/',
                        'reply_markup' => $reply_markup
                    ]);


                    $new_state['state'] = 'main'; // ушли на главную



                    break;


                case 'createDraw': // Ввод название draw


                    $keyboard = [
                        ['Назад, в главное меню'],
                    ];

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);

                    Telegram::sendMessage([
                        'chat_id' => $message['chat']['id'],
                        'text' => 'Пришли мне текст для розыгрыша',
                        'reply_markup' => $reply_markup
                    ]);



                    $new_state['state'] = 'createdDraw';



                    break;


                case 'createdDraw': // Сохранение draw


                    /*  SAVE DRAW  */

                    $double = Shvp::doubleDraw($text, $user_id, $prev_message['date'],$message['date']);


                    if(!$double) {

                        $draw = Draw::create([
                            'text' => $text,
                            'admin_id' => $user_id,
                            'date_finish' => Carbon::now()->addHour(),
                        ]);

                    }


                    if(isset($draw)) {
                        $new_state['draw_id'] = $draw->id;
                    } else {
                        if(isset($prev_draw_id)) {
                            $new_state['draw_id'] = $prev_draw_id;
                        } else {
                            $new_state['draw_id'] = '';
                        }
                    }

                    $draw = Draw::where('id', $new_state['draw_id'])->first();




                    $arSendK = Shvp::firstArSendDraw();

                    $arr = array_merge($arSendK,['chat_id' => $message['chat']['id'], 'text' => $text,]);

                    Telegram::sendMessage($arr);




                    $arDrawSend = Shvp::getArDrawSend($draw);
                    $arMerg = array_merge( $arDrawSend, ['chat_id' => $message['chat']['id']] );


                    $draw_edit_m = Telegram::sendMessage($arMerg);


                    Draw::where('id', $new_state['draw_id'])->update(['edit_message_id' => $draw_edit_m['message_id']]);






                    $new_state['state'] = 'editDraw';



                    break;




                case 'editDrawText': // Сохранение draw

                    $arrSend = Shvp::getArEditFieldDraw();

                    $arMerg = array_merge(
                        $arrSend,
                        [
                            'chat_id' => $message['chat']['id'],
                            'text' => 'Пришли мне новый текст для розыгрыша'
                        ]
                    );

                    Telegram::sendMessage($arMerg);





                    $new_state['state'] = 'editedDrawText';
                    $new_state['draw_id'] = $prev_draw_id;

                    break;



                case 'editedDrawText': // Сохранение drawText

                    $edit_mes_pub = false;

                        $draw = Draw::where('id', $prev_draw_id)->first();
                    if($text != 'Назад, к розыгрышу'){
                        $draw->text = $text;
                        $draw->save();
                        $text_send = $draw->text;
                        if($draw->date_finish > date('Y-m-d H:i:s')) {
                            $edit_mes_pub = true;
                        }
                    } else {
                        $text_send = $draw->text;
                    }


                        $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $text_send);


                        $draw->edit_message_id = $message_new_id['message_id'];
                        $draw->save();

                    if($edit_mes_pub){
                        Shvp::editDrawPublicMessage($draw);
                    }

                        $new_state['state'] = 'editDraw';
                        $new_state['draw_id'] = $prev_draw_id;





                    break;



                case 'editedDrawPart': // Сохранение



                    $draw = Draw::where('id', $prev_draw_id)->first();
                    if($text != 'Назад, к розыгрышу'){
                        $count = (int)$text;
                        if(strlen($count) > 0){
                            $draw->count_part = $text;
                            $draw->save();
                        }
                        $text = $draw->text;
                    } else {
                        $text = $draw->text;
                    }


                    $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $text);



                    $draw->edit_message_id = $message_new_id['message_id'];
                    $draw->save();



                    $new_state['state'] = 'editDraw';
                    $new_state['draw_id'] = $prev_draw_id;





                    break;



                case 'editDrawDateFinish':

                    $arrSend = Shvp::getArEditFieldDraw();

                    $arMerg = array_merge(
                        $arrSend,
                        [
                            'chat_id' => $message['chat']['id'],
                            'text' => 'Пришли мне дату окончания розыгрыша в формате: '. Carbon::now()->addHour() . ' по мск'
                        ]
                    );

                    Telegram::sendMessage($arMerg);



                    $new_state['state'] = 'editedDrawDateFinish';
                    $new_state['draw_id'] = $prev_draw_id;



                    break;




                case 'editedDrawDateFinish': // Сохранение drawText

                    $edit_mes_pub = false;

                    $draw = Draw::where('id', $prev_draw_id)->first();
                    if($text != 'Назад, к розыгрышу'){
                        try
                        {
                            $date_finish = Carbon::parse($text)->format('Y-m-d H:i:s');
                        }
                        catch (\Exception $e)
                        {

                        }


                        if(isset($date_finish)){
                            $draw->date_finish = $date_finish;
                            $draw->save();
                            if($draw->date_finish > date('Y-m-d H:i:s')) {
                                $edit_mes_pub = true;
                            }
                        }
                        $text = $draw->text;
                    } else {
                        $text = $draw->text;
                    }


                    $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $text);

                    $draw->edit_message_id = $message_new_id['message_id'];
                    $draw->save();

                    if($edit_mes_pub){
                        Shvp::editDrawPublicMessage($draw);
                    }



                    $new_state['state'] = 'editDraw';
                    $new_state['draw_id'] = $prev_draw_id;



                    break;


                case 'publicDraw':


                    $draw = Draw::where('id', $prev_draw_id)->first();


                    if($draw->chat_id){
                        // чат установлен

                        if( $draw->count_part > 0 && $draw->public == 0 && !$draw->published_at) {
                            // выбраны спец условия для розыгрыша, публикация такого розыгрыша платная, пройдите по ссылке и получите код публикации стоимость 1300 рублей




                            $keyboard = Keyboard::make()
                                ->inline()
                                ->row(
                                    Keyboard::inlineButton(
                                        [
                                            'text' => 'Получить код публикации на сайте',
                                            'url' => 'https://voterpro.ru/'
                                        ]
                                    )

                                );


                            $reply_markup = $keyboard;


                            $temp_text = 'Выбраны <b>СПЕЦ условия</b> для розыгрыша, публикация такого розыгрыша платная, пройдите по ссылке и получите код публикации стоимость 1400 рублей. Либо введите код, если он у вас есть';


                            Telegram::sendMessage([
                                'chat_id' => $message['chat']['id'],
                                'text' => $temp_text,
                                'parse_mode' => 'HTML',
                                'reply_markup' => $reply_markup
                            ]);



                            $arrSend = Shvp::getArEditFieldDraw();

                            $arMerg = array_merge(
                                $arrSend,
                                [
                                    'chat_id' => $message['chat']['id'],
                                    'text' => 'Введите код публикации, если он у вас есть'
                                ]
                            );

                            Telegram::sendMessage($arMerg);


                            $new_state['state'] = 'payPublic';



                        } else {

                            // Бесплатная публикация

                            // если еще не опубликован
                            if(!$draw->published_at) {

                                $new_mes = Shvp::publicDraw($draw);


                                $draw->published_at = Carbon::now();

                                $draw->status = 'Опубликован';

                                $draw->message_id = $new_mes['message_id'];

                                $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $draw->text);

                                $draw->edit_message_id = $message_new_id['message_id'];
                                $draw->save();

                            } else { // Если уже был опубликован


                                $arrSend = Shvp::getArEditFieldDraw();

                                $arMerg = array_merge(
                                    $arrSend,
                                    [
                                        'chat_id' => $message['chat']['id'],
                                        'text' => 'Данный розыгрыш уже был опубликован'
                                    ]
                                );

                                Telegram::sendMessage($arMerg);




                                $new_state['state'] = 'editDraw';
                                $new_state['draw_id'] = $prev_draw_id;

                            }



                            $new_state['state'] = 'editDraw';

                        }


                    } else {

                        // Нужно назначить чат для публикации

                        $arrSend = Shvp::getArEditFieldDraw();

                        $textSend = 'Нельзя опубликовать не выбрав чат к розыгрышу.';

                        $arMerg = array_merge(
                            $arrSend,
                            [
                                'chat_id' => $message['chat']['id'],
                                'text' => $textSend
                            ]
                        );



                        Telegram::sendMessage($arMerg);



                        $new_state['state'] = 'editDraw';

                    }



                    $new_state['draw_id'] = $prev_draw_id;




                    break;


                case 'payPublic':

                    $paykey = Paykey::where('key', trim($message['text']))->where('payer', '1')->where('draw_id', '0')->first();

                    if($paykey !== null){

                        $paykey->draw_id = $prev_draw_id;

                        $paykey->save();


                        $draw = Draw::where('id', $prev_draw_id)->first();

                        $new_mes = Shvp::publicDraw($draw);


                        $draw->published_at = Carbon::now();
                        $draw->pay_key = $paykey->key;

                        $draw->status = 'Опубликован';

                        $draw->message_id = $new_mes['message_id'];

                        $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $draw->text);

                        $draw->edit_message_id = $message_new_id['message_id'];
                        $draw->save();

                        $new_state['state'] = 'editDraw';
                        $new_state['draw_id'] = $prev_draw_id;

                    } else {


                        $arrSend = Shvp::getArEditFieldDraw();

                        $textSend = 'Код не верный, либо уже активирован.';

                        $arMerg = array_merge(
                            $arrSend,
                            [
                                'chat_id' => $message['chat']['id'],
                                'text' => $textSend
                            ]
                        );



                        Telegram::sendMessage($arMerg);



                        $new_state['state'] = 'editDraw';
                        $new_state['draw_id'] = $prev_draw_id;



                    }







                    break;


                case 'myDrawList':


                    $draws = Draw::where('admin_id', $user_id)->get();

                    $arKey = array();

                    if ($draws->count() > 0) {

                        $keyboard = Keyboard::make()->inline();
                        foreach ($draws as $draw) {
                            $keyboard = $keyboard->row(
                                Keyboard::inlineButton(
                                    [
                                        'text' => $draw->text,
                                        'callback_data' => '(editMyDraw)[' . $draw->id . ']' . Str::random(6)
                                    ]
                                )

                            );
                        }

                        $temp_text = 'Список ваших розыгрышей:';

                        $reply_markup = $keyboard;

                        $arKey = array(
                            'reply_markup' => $reply_markup
                        );

                    } else {
                        $temp_text = 'У вас не создан не один розыгрыш';
                    }


                    $arMerg = array_merge($arKey, [
                        'chat_id' => $message['chat']['id'],
                        'text' => $temp_text,
                        'parse_mode' => 'HTML'
                    ]);


                    Telegram::sendMessage($arMerg);


                    $keyboard = [
                        ['Назад, в главное меню']
                    ];

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);


                    $arMerg = [
                            'chat_id' => $message['chat']['id'],
                            'text' => 'Выберите из списка вами добавленных розыгрышей',
                            'reply_markup' => $reply_markup
                        ];

                    Telegram::sendMessage($arMerg);




                    $new_state['state'] = 'editDraw';



                    break;


                case 'editDrawAddCanal':


                    $draw = Draw::where('id', $prev_draw_id)->first();

                    if($draw->published_at){

                        $arrSend = Shvp::getArEditFieldDraw();

                        $arMerg = array_merge(
                            $arrSend,
                            [
                                'chat_id' => $message['chat']['id'],
                                'text' => 'Нельзя редактировать чат, когда розыгрыш уже опубликован.'
                            ]
                        );



                        Telegram::sendMessage($arMerg);

                    }else {

                        $canals = Canal::where('admin_id', $user_id)->get();

                        if ($canals->count() > 0) {

                            $keyboard = Keyboard::make()->inline();


                            foreach ($canals as $canal) {

                                $keyboard = $keyboard->row(
                                    Keyboard::inlineButton(
                                        [
                                            'text' => $canal->chat_title,
                                            'callback_data' => '(editDrawAddCanal)[' . $canal->id . ']' . Str::random(6)
                                        ]
                                    )

                                );


                            }

                            $temp_text = 'Список ваших чатов:';

                            $reply_markup = $keyboard;
                            $arKeyboard = array('reply_markup' => $reply_markup,);

                            $arMerg = array_merge($arKeyboard, [
                                'chat_id' => $message['chat']['id'],
                                'text' => $temp_text,
                                'parse_mode' => 'HTML'
                            ]);

                        } else {
                            $temp_text = 'Ваш список чатов пуст, зайдите в главное меню "Управление каналами"';

                            $arMerg = [
                                'chat_id' => $message['chat']['id'],
                                'text' => $temp_text,
                                'parse_mode' => 'HTML'
                            ];
                        }





                        Telegram::sendMessage($arMerg);


                        $arrSend = Shvp::getArEditFieldDraw();

                        $arMerg = array_merge(
                            $arrSend,
                            [
                                'chat_id' => $message['chat']['id'],
                                'text' => 'Выберите из списка один из своих чатов, либо зайдите в управление каналами и добавьте свой чат'
                            ]
                        );

                        Telegram::sendMessage($arMerg);




                        $new_state['state'] = 'editedDrawAddCanal';
                        $new_state['draw_id'] = $prev_draw_id;

                    }

                    break;



                case 'editDraw':

                        $draw = Draw::where('id', $prev_draw_id)->first();

                        $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $text);

                        $draw->edit_message_id = $message_new_id['message_id'];
                        $draw->save();

                        $new_state['state'] = 'editDraw';
                        $new_state['draw_id'] = $prev_draw_id;


                    break;



                case 'canalList':

                    $canals = Canal::where('admin_id', $user_id)->get();

                    if($canals->count() > 0){
                        $text = 'Ваши чаты:'. PHP_EOL;
                        foreach($canals as $canal){
                            $text .= $canal->chat_title . PHP_EOL;
                        }

                    } else {
                        $text = 'Добавленных чатов пока нет, нажмите добавить.';
                    }

                    $keyboard = [
                        ['Добавить канал'],
                        ['Назад, в главное меню']
                    ];

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);


                    $arMerg = array(
                        'text' => $text,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $reply_markup,
                        'chat_id' => $message['chat']['id']
                    );

                    Telegram::sendMessage($arMerg);

                    $new_state['state'] = 'canalList';


                    break;



                case 'addCanal':

                    $text = 'Добавь меня в администраторы и пришли мне юзернейм чата.'.PHP_EOL.'Юзернейм чата находится в ссылке (t.me/{username})';

                    $keyboard = [
                        ['Назад, в главное меню']
                    ];

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);


                    $arMerg = array(
                        'text' => $text,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $reply_markup,
                        'chat_id' => $message['chat']['id']
                    );

                    Telegram::sendMessage($arMerg);

                    $new_state['state'] = 'addedCanal';


                    break;



                case 'addedCanal':


                    if($text != 'Назад, к розыгрышу'){


                        $mes = '';

                        $pos = strripos($text, 't.me');

                        if($pos !== false) {

                            $preg = preg_match('/t.me\/(.*)/', $text, $match);

                            $text = $match[1];

                        }


                        if (Canal::where('chat_username', '=', trim($text))->doesntExist()) {

                            $mes = 'Канал, не найден. Возможно меня не добавляли в этот канал.'.PHP_EOL.'Юзернейм канала находится в ссылке (t.me/{username})';

                        } else {
                            $canal = Canal::where('chat_username', '=', trim($text))->first();

                            $canal->admin_id = $user_id;

                            $canal->save();
                        }



                    }


                    Shvp::backCanalList($user_id, $message['chat']['id'], $mes);




                    $new_state['state'] = 'canalList';


                    break;



                case 'backToDraw':

                    $draw = Draw::where('id', $prev_draw_id)->first();

                    $text_send = $draw->text;

                    $message_new_id = Shvp::backDraw($draw, $message['chat']['id'], $text_send);

                    $draw->edit_message_id = $message_new_id['message_id'];
                    $draw->save();

                    $new_state['state'] = 'editDraw';
                    $new_state['draw_id'] = $prev_draw_id;



                    break;



                case 'hello':

                    $text_send = '<b>Я помогу развить канал активностью.</b>' . PHP_EOL .
                        'Устраивайте розыгрыши призов (телефон, скидочный купон и т.д) на своем канале'. PHP_EOL .
                        'Вы можете включить <b>СПЕЦ условия</b> для участия в розыгрыше.' . PHP_EOL .
                        'Например, человек не сможет участвовать пока не добавит в вашу группу 10 (или 50, как вы настроите) своих друзей, знакомых, одноклассников.'. PHP_EOL .
                        'Я сам определяю и считаю добавленных участников в вашу группу и допускаю к участию.'.PHP_EOL.'Сайт бота https://voterpro.ru/';


                    $keyboard = $this->keyboardHello;

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);

                    Telegram::sendMessage([
                        'chat_id' => $message['chat']['id'],
                        'text' => $text_send,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $reply_markup
                    ]);


                    $new_state['state'] = 'main'; // ушли на главную


                    break;



                case 'unknownComand':

                    $text = 'Команда не определена, нажмите пожалуйста /start';

                    Telegram::sendMessage([
                        'chat_id' => $message['chat']['id'],
                        'text' => $text
                    ]);

                    break;



            }












            // Создание админа и сохранение текущего состояния

            if(isset($message['from']['id'])){


                if (Admin::where('user_id', '=', $message['from']['id'])->doesntExist()) {

                    $user_name = ($message['from']['username']) ? '@' . $message['from']['username'] : $message['from']['first_name'];

                    Admin::create([
                        'user_id' => $message['from']['id'],
                        'user_name' => $user_name,
                        'first_name' => $message['from']['first_name']
                    ]);

                }


                Shvp::saveState($message['from']['id'], $new_state);

            }





        } // if private






        // Добавление участников в группу
        if(isset($message['new_chat_participant']) && isset($message['new_chat_members']) ) {



            if($message['new_chat_participant'] && count($message['new_chat_members']) < 2) {

                $partic = $message['new_chat_participant'];

                if(!$partic['is_bot'] && $partic['id'] != $message['from']['id']) {

                    if(Participant::where('part_id', '=', $partic['id'])->where('chat_id', '=', $message['chat']['id'])->doesntExist()) {

                        Participant::create([
                            'member_id' => $message['from']['id'],
                            'chat_id' => $message['chat']['id'],
                            'part_id' => $partic['id'],
                            'first_name' => $partic['first_name'],
                            'user_name' => $partic['username'],
                        ]);

                    }

                }


            } elseif ($message['new_chat_participant'] && count($message['new_chat_members']) > 1) {

                $arMembers = $message['new_chat_members'];

                foreach($arMembers as $member) {

                    if(!$member['is_bot'] && $member['id'] != $message['from']['id']) {


                        if(Participant::where('part_id', '=', $member['id'])->where('chat_id', '=', $message['chat']['id'])->doesntExist()) {

                            Participant::create([
                                'member_id' => $message['from']['id'],
                                'chat_id' => $message['chat']['id'],
                                'part_id' => $member['id'],
                                'first_name' => $member['first_name'],
                                'user_name' => $member['username'],
                            ]);

                        }

                    }


                }

            }



        }





        // Добавление бота в группу, создаем канал и удаляем если выгнали
        if($updates->my_chat_member) {


            $up = $updates->my_chat_member;

            if (Canal::where('chat_id', '=', $up['chat']['id'])->doesntExist()) {

                if($up['new_chat_member']['status'] != 'left' && isset($up['chat']['title'])) {

                    Canal::create([
                        'chat_id' => $up['chat']['id'],
                        'chat_title' => $up['chat']['title'],
                        'chat_username' => $up['chat']['username'],
                    ]);

                }

            } else {

                if($up['new_chat_member']['status'] == 'left') {
                    Canal::where('chat_id', $up['chat']['id'])->delete();
                }


            }





        }


        return 'stop';

    }


    public function respons() {

        /*$draw = Draw::create([
            'text' => '12312',
            'admin_id' => '123123',
            'date_finish' => Carbon::now()->addHour(),
        ]);*/

        $draw = Draw::where('id', 29)->first();


        dd($draw);




        return '';
    }


    public function state() {

        /*$state = Admin::where('user_id', '107685462')->first() ?: false;

        if($state){
            dd(json_decode($state->response, true));
        } else {
            return '';
        }*/

        $res = Shvp::getPrevMessage('107685462');

        dd($res);


    }

    public function drawlist() {

        $draw = Draw::all();

        return view('page.draws', compact('draw'));

    }


    public function deleteDraw($id) {

        Draw::destroy($id);

        return back()->withInput();

    }


    public function test() {
        /*$prev_up = Shvp::getPrevMessage('107685462');
        //$prev_up = Admin::where('user_id', '107685462')->first();

        dd($prev_up);

        if(isset($prev_up['response']['message']))
            $prev_message = $prev_up['response']['message'];
        if(isset($prev_up['state']))
            $prev_state = $prev_up['state'];
        if(isset($prev_up['draw_id']))
            $prev_draw_id = $prev_up['draw_id'];*/

        /*$keyboard = Keyboard::make()
            ->inline()
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => '-Победитель',
                        'callback_data' => '-pob'
                    ]), Keyboard::inlineButton(
                [
                    'text' => '+Победитель',
                    'callback_data' => '+pob'
                ])
            );



        $reply_markup = $keyboard;


        $temp_text = '';
        $temp_text .= '<b>Что вы хотите изменить в розыгрыше ?</b>'. PHP_EOL;



        $draw_edit_m = Telegram::sendMessage([
            'chat_id' => '107685462',
            'text' => $temp_text,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        ]);*/

        /*
        $temp_text = '<b>Что вы хотите изменить в soobshenii ?</b>';

        $arr = [
            'chat_id' => '107685462',
            'message_id' => '462',
            'text' => $temp_text,
            'parse_mode' => 'HTML',
        ];

        $res = Telegram::editMessageText($arr);
        */

       /* $callback['data'] = '(editDraw)[-pob][sdsd]'.Str::random(6);


        $res = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);


        preg_match('/\[.*?\]/', $res, $method);
        preg_match('/\(.*?\)/', $res, $action);


        dd($action[0]);*/

       //$res = Shvp::getPrevMessage('107685462');

/*
        $draw = Draw::where('id', '6')->first();


        $draw->text = '11xczxczc21231312312';
        $draw->save();

        $draw->text_btn = '(5) Участвовать';
        $draw->save();*/

        //$prev_up = Shvp::getPrevMessage('107685462');


        /*
        $date = '2021-07-21 21:00';
        $parse_date = Carbon::parse($date)->format('Y-m-d H:i:s');





        $keyboard1 = Keyboard::make()
            ->inline()
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => '-Победитель',
                        'callback_data' => Str::random(6)
                    ]
                )
            )
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => 'кнопка 1',
                        'callback_data' => Str::random(6)
                    ]
                )
            );



        $array = array(
            ['name' => 'button 1'],
            ['name' => 'button 2'],
            ['name' => 'button 3'],
            ['name' => 'button 4'],
        );


        $keyboard = Keyboard::make()->inline();
        foreach ($array as $chat) {
            $keyboard = $keyboard->row(
                Keyboard::inlineButton([
                    'text' => $chat['name'],
                    'callback_data' => Str::random(6)
                ])
            );
        }


        $callback['data'] = '(editDrawAddCanal)[1312312]' . Str::random(6);


        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);

        preg_match('/\[(.*?)\]/', $res_sub, $method);

        */


        $draw = Draw::where('date_finish', '<', date('Y-m-d H:i:s'))->where('published_at', '<>', '')->get();


        echo date('Y-m-d H:i:s');
        //$draw->


       return dd($draw);



    }


}