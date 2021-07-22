<?php

namespace App\Library;



use App\Admin;
use App\Canal;
use App\Draw;
use App\Member;
use App\Participant;
use Carbon\Carbon;
use Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Str;

class My
{


    public static function doubleDraw($text, $user_id, $prevdate, $date) {
        if(Draw::where('text', $text)->where('admin_id', $user_id)->exists() && $prevdate == $date) {
            return true;
        } else {
            return false;
        }
    }


    // Определение состояния
    public static function getStep($update, $state) {

        $message = $update;
        $text = (isset($message['text'])) ? $message['text'] : '';

        $tg_state = '';

        // ШАГИ
        if($text == 'Назад, в главное меню') {
            $tg_state = 'mainMenu';
        }

        else if ($text == 'Создать розыгрыш') {
            $tg_state = 'createDraw';
        }

        else if ($state == 'createdDraw' && $text) {
            $tg_state = 'createdDraw';
        }

        else if ($text == 'Изменить текст') {
            $tg_state = 'editDrawText';
        }

        else if ($text == 'Время окончания') {
            $tg_state = 'editDrawDateFinish';
        }

        else if ($text == 'Назначить канал/группу') {
            $tg_state = 'editDrawAddCanal';
        }

        else if ($text == 'Опубликовать') {
            $tg_state = 'publicDraw';
        }

        else if ($text == 'Мои розыгрыши') {
            $tg_state = 'myDrawList';
        }

        else if ($state == 'editedDrawText') {
            $tg_state = 'editedDrawText';
        }

        else if ($state == 'editedDrawPart') {
            $tg_state = 'editedDrawPart';
        }

        else if ($state == 'editedDrawDateFinish') {
            $tg_state = 'editedDrawDateFinish';
        }

        else if ($text == 'Назад, к розыгрышу') {
            $tg_state = 'backToDraw';
        }

        else if ($state == 'editDraw') {
            $tg_state = 'editDraw';
        }

        else if ($state == 'payPublic') {
            $tg_state = 'payPublic';
        }



        else if ($text == 'Управление каналами') {
            $tg_state = 'canalList';
        }

        else if ($text == 'Добавить канал' && $state == 'canalList') {
            $tg_state = 'addCanal';
        }

        else if ($state == 'addedCanal') {
            $tg_state = 'addedCanal';
        }

        else if ($text == 'Удалить канал' && $state == 'canalList') {
            $tg_state = 'deleteCanal';
        }

        else if ($text == '/start') {
            $tg_state = 'hello';
        }

        else if (strlen($text) > 1 && $text != '/start') {
            $tg_state = 'unknownComand';
        }




        return $tg_state;

    }




    // Сохранение массива что отправлен в TG
    public static function saveNewMessage($user_id, $message) {

        Admin::where('user_id', $user_id)->update(['response' => json_encode($message)]);

    }

    // Сохранение массива что отправлен в TG
    public static function saveState($user_id, $state) {

        Admin::where('user_id', $user_id)->update(['state' => json_encode($state)]);

    }

    // Получаем старое предыдущее сообщение
    public static function getPrevMessage($user_id) {
        $arRes = Admin::where('user_id', $user_id)->first();
        $res['response'] = json_decode($arRes['response'], true);
        $res['option'] = json_decode($arRes['state'], true);
        return $res;
    }


// ANSWER CALLBACK
    public static function editDraw($prev, $callback)
    {


        $message = $callback['message'];
        $user_id = $message['chat']['id'];
        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);

        preg_match('/\[.*?\]/', $res_sub, $method);
        preg_match('/\(.*?\)/', $res_sub, $action);

        $method = $method[0];
        $action = $action[0];

        if(isset($prev['option']['draw_id']))
            $draw_id = $prev['option']['draw_id'];

        $draw = Draw::where('id', $draw_id)->first();



        if($draw->edit_message_id == $message['message_id']) {

            $edit_message_id = $message['message_id'];

            $edit_mes_pub = false;

            switch ($method) {
                case '[-pob]':


                    //$draw->edit_message_id
                    if ($draw->count_victory > 1) {
                        $draw->count_victory--;
                        $mes = 'Количество победителей уменьшилось на 1';

                        if($draw->date_finish > date('Y-m-d H:i:s')) {
                            $edit_mes_pub = true;
                        }
                    } else {
                        $mes = 'Нельзя поставить меньше чем 1 победителя';
                    }


                    break;


                case '[+pob]':


                    $draw->count_victory++;
                    $mes = 'Количество победителей увеличилось на 1';

                    if($draw->date_finish > date('Y-m-d H:i:s')) {
                        $edit_mes_pub = true;
                    }


                    break;

                case '[+newpart]':


                    if($draw->published_at){

                        $mes = 'Нельзя менять, когда розыгрышь уже опубикован';

                    } else {
                        $draw->new_part = 1;
                        $mes = 'Включен отсчет приглашенных только после старта розыгрыша'. PHP_EOL .'Условие работает если вы ограничивате допуск к розыгрышу по количеству приглашенных в группу';
                    }



                    break;

                case '[-newpart]':

                    if($draw->published_at){

                        $mes = 'Нельзя менять, когда розыгрышь уже опубикован';

                    } else {
                        $draw->new_part = 0;
                        $mes = 'Включен отсчет приглашенных за все время нахождения в группе' . PHP_EOL . 'Условие работает если вы ограничивате допуск к розыгрышу по количеству приглашенных в группу';
                    }

                    break;

                case '[countpart]':


                    $draw = Draw::where('id', $draw_id)->first();
                    $arrSend = self::getArEditFieldDraw();

                    if($draw->published_at){

                        $textSend = 'Нельзя менять условие участия: количество приглашенных, когда розыгрыш опубликован';

                        $new_state['state'] = 'editDraw';


                    } else {

                        $textSend = 'Напиши количество приглашенных (цифра)';

                        $new_state['state'] = 'editedDrawPart';
                    }


                    $arMerg = array_merge(
                        $arrSend,
                        [
                            'chat_id' => $message['chat']['id'],
                            'text' => $textSend
                        ]
                    );



                    Telegram::sendMessage($arMerg);

                    unset($arrSend);
                    unset($arMerg);




                    $new_state['draw_id'] = $draw_id;

                    self::saveState($user_id, $new_state);



                    break;
            }

            if($method != '[countpart]'):


                $arDrawSend = self::getArDrawSend($draw);


                $arMerg = array_merge($arDrawSend, ['chat_id' => $message['chat']['id'], 'message_id' => $edit_message_id]);


                Telegram::editMessageText($arMerg);

                $param = [
                    'callback_query_id' => $callback['id'],
                    'cache_time' => 0,
                    'text' => $mes,
                    'show_alert' => true
                ];

                Telegram::answerCallbackQuery($param);


                $draw->save();


                if($edit_mes_pub){

                    self::editDrawPublicMessage($draw);

                }


            endif;


        }



    }


    public static function editDrawCanal($prev, $callback) {


        $message = $callback['message'];
        $user_id = $message['chat']['id'];

        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
        preg_match('/\[(.*?)\]/', $res_sub, $method);

        $canal_id = $method['1'];

        $canal = Canal::where('id', $canal_id)->first();



        $draw = Draw::where('id', $prev['option']['draw_id'])->first();

        $draw->chat_id = $canal->chat_id;
        $draw->chat_title = $canal->chat_title;


        $draw->save();


        $message_new_id = self::backDraw($draw, $message['chat']['id'], $draw->text);

        $draw->edit_message_id = $message_new_id['message_id'];
        $draw->save();

        $new_state['state'] = 'editDraw';
        $new_state['draw_id'] = $prev['option']['draw_id'];

        self::saveState($user_id, $new_state);







    }




    public static function listEditMyDraw($prev, $callback) {


        $message = $callback['message'];
        $user_id = $message['chat']['id'];

        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
        preg_match('/\[(.*?)\]/', $res_sub, $method);

        $draw_id = $method['1'];

        $draw = Draw::where('id', $draw_id)->first();

        $message_new_id = self::backDraw($draw, $user_id, $draw->text);

        $draw->edit_message_id = $message_new_id['message_id'];
        $draw->save();

        $new_state['state'] = 'editDraw';
        $new_state['draw_id'] = $draw_id;




    }





    public static function getArEditFieldDraw() {

        $keyboard = [
            ['Назад, к розыгрышу'],
        ];

        $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);

        return $arr = array(
            'reply_markup' => $reply_markup
        );


    }


    public static function getArDrawSend($draw) {


        $keyboard = Keyboard::make()
            ->inline()
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => '-Победитель',
                        'callback_data' => '(editDraw)[-pob]' . Str::random(6)
                    ]
                ),
                Keyboard::inlineButton(
                    [
                        'text' => '+Победитель',
                        'callback_data' => '(editDraw)[+pob]' . Str::random(6)
                    ]
                )
            )
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => 'Количество приглашенных',
                        'callback_data' => '(editDraw)[countpart]' . Str::random(6)
                    ]
                )

            )
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => 'Приглашенные после старта',
                        'callback_data' => '(editDraw)[+newpart]' . Str::random(6)
                    ]
                )

            )
            ->row(
                Keyboard::inlineButton(
                    [
                        'text' => 'Приглашенные за все время',
                        'callback_data' => '(editDraw)[-newpart]' . Str::random(6)
                    ]
                )
            );


        $reply_markup = $keyboard;

        $new_part = ($draw->new_part == 0) ? 'Нет' : 'Да';
        $drawchat_title = (isset($draw->chat_title)) ? $draw->chat_title : 'Не определен';

        $temp_text = '';
        $temp_text .= '<b>Что вы хотите изменить в розыгрыше ?</b>' . PHP_EOL;
        $temp_text .= 'Текст розыгрыша: ' . $draw->text . PHP_EOL;
        $temp_text .= 'Время окончания: ' . $draw->date_finish . PHP_EOL;
        $temp_text .= 'Канал/группа: ' . $drawchat_title . PHP_EOL;
        $temp_text .= 'Количество победителей: ' . $draw->count_victory . PHP_EOL;
        $temp_text .= 'Статус: ' . $draw->status . PHP_EOL;
        $temp_text .= '<b>СПЕЦ Условия для участия в розыгрыше: </b>' . PHP_EOL;
        $temp_text .= 'Количество приглашенных в группу: ' . $draw->count_part . PHP_EOL;
        $temp_text .= 'Считать приглашенных только после старта розыгрыша ? ' . $new_part . PHP_EOL;


        $arDraw = array(
            'text' => $temp_text,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        );


        return $arDraw;


    }


    public static function firstArSendDraw() {

        $keyboard = [
            ['Изменить текст', 'Время окончания'],
            ['Назначить канал/группу', 'Опубликовать'],
            ['Назад, в главное меню']
        ];

        $reply_markup = new Keyboard(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);


        $arr = array(
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        );

        return $arr;


    }



    public static function backDraw($draw, $chat_id, $text = '') {


        // Первое сообщение с названием розыгрыша и меню
        $arrSend = self::firstArSendDraw();
        $arrSend = array_merge($arrSend, ['chat_id' => $chat_id, 'text' => $text,]);

        Telegram::sendMessage($arrSend);


        // Второе сообщение с описанием розыгрыша и встроенным меню
        $arDrawSend = self::getArDrawSend($draw);
        $arMerg = array_merge( $arDrawSend, ['chat_id' => $chat_id] );


        $draw_edit_m = Telegram::sendMessage($arMerg);
        return $draw_edit_m;

    }


    public static function backCanalList($user_id, $chat_id, $text = '') {


        $canals = Canal::where('admin_id', $user_id)->get();

        if($canals->count() > 0){
            $text = 'Ваши чаты:'. PHP_EOL;
            foreach($canals as $canal){
                $text .= $canal->chat_title . PHP_EOL;
            }

        } else {
            if(strlen($text) < 2)
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
            'chat_id' => $chat_id
        );

        Telegram::sendMessage($arMerg);



    }



    public static function publicDraw($draw) {


        $keyboard = Keyboard::make()
            ->inline()
            ->row(
                Keyboard::inlineButton([
                    'text' => '(0) Участвовать',
                    'callback_data' => '(vote)['.$draw->id.']'.Str::random(8)
                ])
            );

        $reply_markup = $keyboard;

        $text = '';

        $text .= "<b>".$draw->text."</b>".PHP_EOL;
        $text .= "Участников: 0 чел.".PHP_EOL;
        $text .= "Победителей: " . $draw->count_victory . PHP_EOL;
        $text .= "Продлится до ". Carbon::parse($draw->date_finish)->format('Y-m-d H:i') . " по мск.";


        $arr = array(
            'chat_id' => $draw->chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $reply_markup
        );

        $message_id = Telegram::sendMessage($arr);

        return $message_id;


    }



    public static function editDrawPublicMessage($draw) {

        if($draw->message_id) {

            $arMembers = Member::where('draw_id', $draw->id)->get();
            $members_count = $arMembers->count();

            $keyboard = Keyboard::make()
                ->inline()
                ->row(
                    Keyboard::inlineButton([
                        'text' => '('.$members_count.') Участвовать',
                        'callback_data' => '(vote)[' . $draw->id . ']' . Str::random(8)
                    ])
                );

            $reply_markup = $keyboard;

            $text = '';

            $text .= "<b>" . $draw->text . "</b>" . PHP_EOL;
            $text .= "Участников: ".$members_count." чел." . PHP_EOL;
            $text .= "Победителей: " . $draw->count_victory . PHP_EOL;
            $text .= "Продлится до " . Carbon::parse($draw->date_finish)->format('Y-m-d H:i') . " по мск.";


            $arr = array(
                'chat_id' => $draw->chat_id,
                'text' => $text,
                'message_id' => $draw->message_id,
                'parse_mode' => 'HTML',
                'reply_markup' => $reply_markup
            );


            Telegram::editMessageText($arr);

        }


    }



    public static function addMembers($callback) {


        $up = $callback;
        $message = $callback['message'];
        $user_id = $callback['from']['id'];
        $user_name = (isset($callback['from']['username'])) ? '@' . $callback['from']['username'] : $callback['from']['first_name'];
        $first_name = $callback['from']['first_name'];

        $res_sub = substr($callback['data'], 0, strpos($callback['data'], ']') + 1);
        preg_match('/\[(.*?)\]/', $res_sub, $method);

        $draw_id = $method['1'];



        $resMember = self::checkSubscribe($message['chat']['id'], $user_id);



        if($resMember && $resMember['status'] != 'kicked' && $resMember['status'] != 'left' && !$callback['from']['is_bot']) {

            $draw = Draw::where('id', $draw_id)->first();

            $new_part = ($draw->new_part == 0) ? false : true;
            $count_part = $draw->count_part;

            $addMemberSt = true;

            $param = [
                'callback_query_id' => $callback['id'],
                'cache_time' => 0,
                'show_alert' => true
            ];


            if ($count_part > 0) {
                $addMemberSt = false;

                if ($new_part) {
                    $arParts = Participant::where('member_id', $user_id)->where('chat_id', $message['chat']['id'])->where('created_at', '>', $draw->published_at)->get();
                } else {
                    $arParts = Participant::where('member_id', $user_id)->where('chat_id', $message['chat']['id'])->get();
                }

                $count_mypart = $arParts->count();

                if ($count_mypart >= $count_part) {
                    $addMemberSt = true;
                }
            }





            // Если еще не участвует
            if (Member::where('user_id', $user_id)->where('draw_id', $draw_id)->doesntExist()) {


                // Если хватает приглашенных либо вообще не нужны
                if ($addMemberSt) {

                    Member::create([
                        'draw_id' => $draw_id,
                        'user_id' => $user_id,
                        'user_name' => $user_name,
                        'first_name' => $first_name,
                    ]);


                    $param['text'] = 'Вы стали участником!';

                } else {

                    $count_send_mypart = (isset($count_mypart)) ? $count_mypart : 0;
                    $param['text'] = 'Для участия добавьте ' . $count_part . ' друга в эту группу! ' . PHP_EOL;
                    $param['text'] .= 'Обязательное условие!' . PHP_EOL;;
                    $param['text'] .= 'Вы уже добавили ' . $count_send_mypart . ' участников.';

                }

            } else { // Уже участвует
                $param['text'] = 'Вы участвуете!';
            }

        } else {


            $param['text'] = 'Доступно только для подписчиков группы';

        }






        Telegram::answerCallbackQuery($param);

        self::editDrawPublicMessage($draw);






    }




















    public static function checkSubscribe($chat_id, $user_id) {


        $arMember = array(
            'chat_id' => $chat_id,
            'user_id' => $user_id
        );

        try
        {
            $resMember = Telegram::getChatMember($arMember);
        }
        catch (\Exception $e)
        {

        }

        if(isset($resMember)){
            return $resMember;
        } else {
            return false;
        }

    }

}