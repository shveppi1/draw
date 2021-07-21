@extends('drawlist')


@section('content')


    <table class="table table-responsive table-striped ">
    <thead>

        <tr>
            <th>id</th>
            <th>admin_id</th>
            <th>chat_id</th>
            <th>message_id</th>
            <th>text</th>
            <th>text_btn</th>
            <th>new_part</th>
            <th>count_part</th>
            <th>count_victory</th>
            <th>pay_key</th>
            <th>public</th>
            <th>date_finish</th>
            <th>published_at</th>
            <th>Действия</th>
        </tr>

    </thead>

    <tbody>
    @foreach ($draw as $dr)
        <tr>
        <td>{{ $dr->id }}</td>
        <td>{{ $dr->admin_id }}</td>
        <td>{{ $dr->chat_id }}</td>
        <td>{{ $dr->message_id }}</td>
        <td>{{ $dr->text }}</td>
        <td>{{ $dr->text_btn }}</td>
        <td>{{ $dr->new_part }}</td>
        <td>{{ $dr->count_part }}</td>
        <td>{{ $dr->count_victory }}</td>
        <td>{{ $dr->pay_key }}</td>
        <td>{{ $dr->public }}</td>
        <td>{{ $dr->date_finish }}</td>
        <td>{{ $dr->published_at }}</td>
            <td><a href="{{route('deleteDraw', ['id' => $dr->id])}}">Удалить</a></td>
        </tr>
    @endforeach
    </tbody>

    </table>



    <form method="POST" action="https://yoomoney.ru/quickpay/confirm.xml">
        <input type="hidden" name="receiver" value="410014133809236">
        <input type="hidden" name="formcomment" value="Получение промокода для публикации">
        <input type="hidden" name="short-dest" value="Спасибо, за вашу поддержку">
        <input type="hidden" name="label" value="16220">
        <input type="hidden" name="quickpay-form" value="shop">
        <input type="hidden" name="targets" value="транзакция 4132">
        <input type="hidden" name="sum" value="1600.00" data-type="number">
        <input type="hidden" name="comment" value="Хотелось бы получить промокод.">
        <input type="hidden" name="need-fio" value="false">
        <input type="hidden" name="need-email" value="true">
        <input type="hidden" name="need-phone" value="false">
        <input type="hidden" name="need-address" value="false">
        <label><input type="hidden" name="paymentType" value="AC">Банковской картой</label>
        <input type="submit" value="Перевести">
    </form>

    <iframe src="https://yoomoney.ru/quickpay/button-widget?targets=%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B8%D1%82%D1%8C%20%D0%BF%D1%80%D0%BE%D0%BC%D0%BE%D0%BA%D0%BE%D0%B4%20%D0%B4%D0%BB%D1%8F%20%D0%BF%D1%83%D0%B1%D0%BB%D0%B8%D0%BA%D0%B0%D1%86%D0%B8%D0%B8&default-sum=1600&button-text=11&any-card-payment-type=on&button-size=m&button-color=orange&mail=on&successURL=https%3A%2F%2Fvoterpro.ru%2Fthanks%2F&quickpay=small&account=410014133809236&" width="184" height="36" frameborder="0" allowtransparency="true" scrolling="no"></iframe>






@endsection