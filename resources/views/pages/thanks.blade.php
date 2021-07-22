@extends('voterpro')


@section('content')


    <div class="cont_shvp">

        <section class="hero">
            <div class="container">
                <div class="hero-inner">
                    <div class="hero-copy">
                        <h1 class="hero-title mt-0">Спасибо за оплату!</h1>
                        <p class="hero-paragraph">Ваш промокод публикации так же отправлен вам на указанную почту.</p>
                        <div class="hero-cta">

                            @empty(!$paykey)
                                <span style="font-size: 20px;">Промокод публикации</span>: <strong>{{ $paykey }}</strong>
                            @endempty

                        </div>
                    </div>

                    <div class="hero-app">
                        <div class="hero-copy">
                        <div class="thanks_img">

                        </div>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
        </section>

    </div>




@endsection