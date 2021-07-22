@component('mail::message')
# Ваш промокод

{{$value['promo']}}


Спасибо,<br>
{{ config('app.name') }} - Создай розыгрыш!
@endcomponent
