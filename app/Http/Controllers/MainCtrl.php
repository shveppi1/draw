<?php

namespace App\Http\Controllers;


use App\Admin;
use App\Canal;
use App\Champion;
use App\Draw;
use App\Member;
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




                    case '(editMyDrawComplited)':


                        $message = $callback['message'];
                        $user_id = $message['chat']['id'];

                        // Получаем ID розыгрыша
                        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
                        preg_match('/\[(.*?)\]/', $res_sub, $method);

                        $draw_id = $method['1'];

                        $draw = Draw::where('id', $draw_id)->first();

                        $message_new_id = Shvp::editComplitedDraw($draw, $user_id, $draw->text);

                        $draw->edit_message_id = $message_new_id['message_id'];
                        $draw->save();

                        $new_state['state'] = 'myDrawListComplited';
                        $new_state['draw_id'] = $draw->id;


                        Shvp::saveState($user_id, $new_state);



                        break;


                    case '(rerolVictoryComplitedDraw)':

                        $message = $callback['message'];
                        $user_id = $message['chat']['id'];

                        // Получаем ID розыгрыша
                        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
                        preg_match('/\[(.*?)\]/', $res_sub, $method);

                        $draw_id = $method['1'];

                        $draw = Draw::where('id', $draw_id)->first();





                        Champion::where('draw_id', $draw->id)->delete();

                        $members = Member::where('draw_id', $draw->id)->inRandomOrder()->limit($draw->count_victory)->get();

                        $victory_send = '';

                        if ($members->count()) {

                            // Собираем и сохраняем победителей
                            foreach ($members as $member) {

                                Champion::create([
                                    'user_id' => $member->user_id,
                                    'draw_id' => $member->draw_id,
                                    'user_name' => $member->user_name,
                                    'first_name' => $member->first_name,
                                ]);


                                $user_name = (isset($member->user_name)) ? $member->user_name : $member->first_name;

                                $victory_send .= '<a href="tg://user?id=' . $member->user_id . '">' . $user_name . '</a>' . PHP_EOL;

                                unset($user_name);

                            }

                        } else {
                            $victory_send = 'Победителей нет. Ни одного участника не было';
                        }


                        $temp_text = '';
                        $temp_text .= 'Текст розыгрыша: ' . $draw->text . PHP_EOL;
                        $temp_text .= 'Время окончания: ' . $draw->date_finish . PHP_EOL;
                        $temp_text .= 'Группа: ' . $draw->chat_title . PHP_EOL;
                        $temp_text .= 'Победители: ' . $victory_send . PHP_EOL;


                        $keyboard = Keyboard::make()
                            ->inline()
                            ->row(
                                Keyboard::inlineButton(
                                    [
                                        'text' => 'Перевыбрать победителей',
                                        'callback_data' => '(rerolVictoryComplitedDraw)['.$draw->id.']' . Str::random(6)
                                    ]
                                )

                            );

                        // Меняем сообщение у админа
                        $reply_markup = $keyboard;
                        $arr = array(
                            'chat_id' => $message['chat']['id'],
                            'text' => $temp_text,
                            'message_id' => $draw->edit_message_id,
                            'parse_mode' => 'HTML',
                            'reply_markup' => $reply_markup
                        );


                        Telegram::editMessageText($arr);



                        // Меняем сообщение в публичном чате
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
                            Telegram::editMessageText($arrSend);
                        }
                        catch (Telegram\Bot\Exceptions\TelegramResponseException $err) {

                        }






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



            $logTxt = print_r($updates, true);

            Log::channel('tgprivate')->info($logTxt);


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
                        'text' => 'Устраивайте розыгрыши призов (телефон, скидочный купон и т.д) на своем канале за подписку, и приглашение участников в группу.' . PHP_EOL . 'Я помогу Вам развить канал'. PHP_EOL . 'Тех. поддержка: @shveeps',
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
                            'text' => 'Пришли мне дату окончания розыгрыша' . PHP_EOL . 'в формате: '. Carbon::now()->addHour()->format('Y-m-d H:i') . ' по мск'
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


                            $temp_text = 'Выбраны <b>СПЕЦ условия</b> для розыгрыша, публикация такого розыгрыша платная,'.PHP_EOL.'пройдите по ссылке и получите код публикации стоимость 1100 рублей'.PHP_EOL.PHP_EOL.'Опубликовать бесплатно если поставить количество приглашенных 0.';


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


                    $draws = Draw::where('admin_id', $user_id)->where('status', '!=', 'Завершен')->get();

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
                        ['Завершенные'],
                        ['Назад, в главное меню']
                    ];

                    $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);

                    $text_notFin = ($draws->count() > 0) ?  PHP_EOL . 'Завершенные розыгрыши в этом списке не отображаются' : '';

                    $arMerg = [
                            'chat_id' => $message['chat']['id'],
                            'text' => 'Выберите из списка вами добавленных розыгрышей.' . $text_notFin,
                            'reply_markup' => $reply_markup
                        ];

                    Telegram::sendMessage($arMerg);




                    $new_state['state'] = 'main';



                    break;



                case 'myDrawListComplited':


                    Shvp::myDrawListComplited($message, $user_id);

                    $new_state['state'] = 'myDrawListComplited';



                    break;

                // Убираем админа с розыгрыша
                case 'deleteDrawComplited':


                    $draw = Draw::where('id', $prev_draw_id)->first();
                    $draw->admin_id = 0;
                    $draw->save();
                    Shvp::myDrawListComplited($message, $user_id);
                    $new_state['state'] = 'myDrawListComplited';

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

                        $new_state['state'] = 'editDraw';
                        $new_state['draw_id'] = $prev_draw_id;

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

                    $text = 'Добавь меня в администраторы группы!'.PHP_EOL.
                        'Напиши сюда юзернейм чата.'.PHP_EOL.
                        'Юзернейм чата находится в ссылке (t.me/{username})'.PHP_EOL.PHP_EOL.
                        'Либо пропишите в вашей группе команду /voterPro (вы должны быть администратором)'.PHP_EOL.
                        'В списке появится ваша группа';

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
                        'Устраивайте розыгрыши призов (телефон, скидочный купон и т.д) на своем канале'. PHP_EOL . PHP_EOL .
                        'Вы можете включить <b>СПЕЦ условия</b> для участия в розыгрыше.' . PHP_EOL . PHP_EOL .
                        'Например, человек не сможет участвовать пока не добавит в вашу группу 10 (или 50, как вы настроите) своих друзей, знакомых, одноклассников.'. PHP_EOL . PHP_EOL .
                        'Я сам определяю и считаю добавленных участников в вашу группу и допускаю к участию.'.PHP_EOL.'Сайт бота https://voterpro.ru/'. PHP_EOL .
                        'Тех. поддержка: @shveeps';


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

                    $new_state['state'] = 'main'; // ушли на главную

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


        // Написали что то в чате групп
        if( $updates->message && $message['chat']['type'] != 'private' && isset($message['text']) ) {


            // Назначаем админа группы
            if(isset($message['text']) && $message['text'] == '/voterPro') {


                $logTxt = print_r($updates, true);

                Log::channel('test')->info($logTxt);

                $from['user_id'] = $message['from']['id'];
                $from['user_name'] = ( isset($message['from']['username']) ) ? '@' . $message['from']['username'] : $message['from']['first_name'];
                $from['first_name'] = $message['from']['first_name'];
                $chat['chat_id'] = $message['chat']['id'];




                try
                {
                    $arMember = array(
                        'chat_id' => $chat['chat_id'],
                        'user_id' => $from['user_id']
                    );

                    $resMember = Telegram::getChatMember($arMember);

                    if( $resMember['status'] == 'creator' || $resMember['status'] == 'administrator' ){
                        $admin_status_chat = true;
                    }

                }
                catch (\Exception $e)
                {

                }

                if(isset($admin_status_chat)){
                    if(Admin::where('user_id', $from['user_id'])->exists()){

                        $canal = Canal::where('chat_id', $chat['chat_id'])->first();

                        $canal->admin_id = $from['user_id'];

                        $canal->save();

                    }
                }




            }


        }



        // Поменялся тип у группы
        if( $updates->message && $message['chat']['type'] != 'private' && isset($message['migrate_to_chat_id']) ) {

            $chat['chat_id'] = $message['chat']['id'];

            $canal = Canal::where('chat_id', $chat['chat_id'])->first();

            $canal->chat_id = $message['migrate_to_chat_id'];

            $canal->save();

        }


        // Удаление канала если бота выгнали из него
        if( $updates->message && $message['chat']['type'] != 'private' && isset($message['left_chat_member']) ) {

            if(isset($message['left_chat_member']['username']) && $message['left_chat_member']['username'] == 'VoterPro_Bot'){
                $chat['chat_id'] = $message['chat']['id'];
                Canal::where('chat_id', $chat['chat_id'])->delete();
            }


        }


        // Добавление участников в группу
        if(isset($message['new_chat_participant']) && isset($message['new_chat_members']) ) {



            if($message['new_chat_participant'] && count($message['new_chat_members']) < 2) {

                $partic = $message['new_chat_participant'];

                if(!$partic['is_bot'] && $partic['id'] != $message['from']['id']) {

                    if(Participant::where('part_id', '=', $partic['id'])->where('chat_id', '=', $message['chat']['id'])->doesntExist()) {

                        $arPartAdd = [
                            'member_id' => $message['from']['id'],
                            'chat_id' => $message['chat']['id'],
                            'part_id' => $partic['id'],
                            'first_name' => $partic['first_name'],
                        ];
                        if(isset($partic['username'])){
                            $arPartAdd['user_name'] = $partic['username'];
                        } else {
                            $arPartAdd['user_name'] = $partic['first_name'];
                        }

                        Participant::create($arPartAdd);

                        unset($arPartAdd);

                    }

                }


            } elseif ($message['new_chat_participant'] && count($message['new_chat_members']) > 1) {

                $arMembers = $message['new_chat_members'];

                foreach($arMembers as $member) {

                    if(!$member['is_bot'] && $member['id'] != $message['from']['id']) {


                        if(Participant::where('part_id', '=', $member['id'])->where('chat_id', '=', $message['chat']['id'])->doesntExist()) {


                            $arPartAdd = [
                                'member_id' => $message['from']['id'],
                                'chat_id' => $message['chat']['id'],
                                'part_id' => $member['id'],
                                'first_name' => $member['first_name'],
                            ];

                            if(isset($member['username'])) {
                                $arPartAdd['user_name'] = $member['username'];
                            }else {
                                $arPartAdd['user_name'] = $member['first_name'];
                            }

                            Participant::create($arPartAdd);

                        }

                    }


                }

            }



        }





        // Добавление бота в группу, создаем канал и удаляем если выгнали
        if($updates->my_chat_member) {


            $up = $updates->my_chat_member;

            if (Canal::where('chat_id', $up['chat']['id'])->doesntExist()) {

                // Добавляем канал
                if($up['new_chat_member']['status'] != 'left' && isset($up['chat']['title'])) {

                    $arrAdd = [
                        'chat_id' => $up['chat']['id'],
                        'chat_title' => $up['chat']['title'],

                    ];

                    if(isset($up['chat']['username'])) {
                        $arrAdd['chat_username'] = $up['chat']['username'];
                    }

                    $canal = Canal::create($arrAdd);

                    unset($arrAdd);


                    if($up['new_chat_member']['status'] == 'administrator' && isset($up['chat']['type'])) {

                        if($up['chat']['type'] == 'channel'){
                            $chat['chat_id'] = $up['chat']['id'];
                            $from['user_id'] = $up['from']['id'];

                            try
                            {
                                $arMember = array(
                                    'chat_id' => $chat['chat_id'],
                                    'user_id' => $from['user_id']
                                );

                                $resMember = Telegram::getChatMember($arMember);

                                if( $resMember['status'] == 'creator' || $resMember['status'] == 'administrator' ){
                                    $admin_status_chat = true;
                                }

                            }
                            catch (\Exception $e)
                            {

                            }

                            if(isset($admin_status_chat)) {
                                $admin = Admin::where('user_id', $from['user_id'])->first();

                                if($admin){
                                    $canal->admin_id = $from['user_id'];
                                    $canal->save();
                                }
                            }

                        }

                    }


                }

            } else {

                if($up['new_chat_member']['status'] == 'left') {
                    Canal::where('chat_id', $up['chat']['id'])->delete();
                }


            }





        }


        return 'stop';

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


        $arMember = array(
            'chat_id' => '-495557507',
            'user_id' => '107685462'
        );

        //$resMember = Telegram::getChatMember($arMember);

        $arMember = array(
            'chat_id' => '-1001222902126',
        );

        //$resMember = Telegram::getChatAdministrators($arMember);



        //1604951343

        /*


        $arMember = array(
            'chat_id' => '-1001311691334',
            'user_id' => '1604951343'
        );

        $resMember = Telegram::getChatMember($arMember);



        dd($resMember);

        */


        $arr = array(
            'chat_id' => '107685462',
            'text' => 'ссылка <a href="tg://user?id=1604951343">shvep</a>',
            'parse_mode' => 'HTML'
        );

        Telegram::sendMessage($arr);






        echo date('Y-m-d H:i:s');
        //$draw->


       return dd([]);



    }


}