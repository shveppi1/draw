@extends('voterpro')


@section('content')

<section class="hero">
    <div class="container">
        <div class="hero-inner">
            <div class="hero-copy">
                <h1 class="hero-title mt-0">VoterPro - создай розыгрыш в чате</h1>
                <p class="hero-paragraph">Создайте активность в группе телеграмм.<br />Устраивайте розыгрыши призов (телефон, скидочный купон и т.д) на своем канале.</p>
                <div class="hero-cta">
                    <a class="button button-shadow" href="https://t.me/VoterPro_Bot">Создать</a>
                    <a class="button button-primary button-shadow" href="#pay">Получить код</a>
                </div>
            </div>
            <div class="hero-app">
                <div class="hero-app-illustration">
                   {{--

                    <!--svg width="999" height="931" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient x1="92.827%" y1="0%" x2="53.422%" y2="80.087%" id="hero-shape-a">
                                <stop stop-color="#F9425F" offset="0%"/>
                                <stop stop-color="#F97C58" stop-opacity="0" offset="100%"/>
                            </linearGradient>
                            <linearGradient x1="92.827%" y1="0%" x2="53.406%" y2="80.12%" id="hero-shape-b">
                                <stop stop-color="#47A1F9" offset="0%"/>
                                <stop stop-color="#F9425F" stop-opacity="0" offset="80.532%"/>
                                <stop stop-color="#FDFFDA" stop-opacity="0" offset="100%"/>
                            </linearGradient>
                            <linearGradient x1="8.685%" y1="23.733%" x2="85.808%" y2="82.837%" id="hero-shape-c">
                                <stop stop-color="#FFF" stop-opacity=".48" offset="0%"/>
                                <stop stop-color="#FFF" stop-opacity="0" offset="100%"/>
                            </linearGradient>
                            <linearGradient x1="79.483%" y1="15.903%" x2="38.42%" y2="70.124%" id="hero-shape-d">
                                <stop stop-color="#47A1F9" offset="0%"/>
                                <stop stop-color="#FDFFDA" stop-opacity="0" offset="100%"/>
                            </linearGradient>
                            <linearGradient x1="99.037%" y1="26.963%" x2="24.582%" y2="78.557%" id="hero-shape-e">
                                <stop stop-color="#FDFFDA" stop-opacity=".64" offset="0%"/>
                                <stop stop-color="#F97C58" stop-opacity=".24" offset="42.952%"/>
                                <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                            </linearGradient>
                        </defs>
                        <g fill="none" fill-rule="evenodd">
                            <g class="hero-shape-top">
                                <g class="is-moving-object is-translating" data-translating-factor="280">
                                    <path d="M680.188 0c-23.36 69.79-58.473 98.3-105.34 85.531-70.301-19.152-189.723-21.734-252.399 91.442-62.676 113.175-144.097 167.832-215.195 118.57C59.855 262.702 24.104 287.85 0 370.988L306.184 566.41c207.164-4.242 305.67-51.612 295.52-142.11-10.152-90.497 34.533-163.55 134.054-219.16l4.512-119.609L680.188 0z" fill="url(#hero-shape-a)" transform="translate(1)"/>
                                </g>
                                <g class="is-moving-object is-translating" data-translating-factor="100">
                                    <path d="M817.188 222c-23.36 69.79-58.473 98.3-105.34 85.531-70.301-19.152-189.723-21.734-252.399 91.442-62.676 113.175-144.097 167.832-215.195 118.57-47.399-32.841-83.15-7.693-107.254 75.445L443.184 788.41c207.164-4.242 305.67-51.612 295.52-142.11-10.152-90.497 34.533-163.55 134.054-219.16l4.512-119.609L817.188 222z" fill="url(#hero-shape-b)" transform="rotate(-53 507.635 504.202)"/>
                                </g>
                            </g>
                            <g transform="translate(191 416)">
                                <g class="is-moving-object is-translating" data-translating-factor="50">
                                    <circle fill="url(#hero-shape-c)" cx="336" cy="190" r="190"/>
                                </g>
                                <g class="is-moving-object is-translating" data-translating-factor="80">
                                    <path d="M683.766 133.043c-112.048-90.805-184.688-76.302-217.92 43.508-33.23 119.81-125.471 124.8-276.722 14.972-3.156 120.356 53.893 200.09 171.149 239.203 175.882 58.67 346.695-130.398 423.777-239.203 51.388-72.536 17.96-92.03-100.284-58.48z" fill="url(#hero-shape-d)"/>
                                </g>
                                <g class="is-moving-object is-translating" data-translating-factor="100">
                                    <path d="M448.206 223.247c-97.52-122.943-154.274-117.426-170.26 16.55C261.958 373.775 169.717 378.766 1.222 254.77c-9.255 95.477 47.794 175.211 171.148 239.203 185.032 95.989 424.986-180.108 424.986-239.203 0-39.396-49.717-49.904-149.15-31.523z" fill="url(#hero-shape-e)" transform="matrix(-1 0 0 1 597.61 0)"/>
                                </g>
                            </g>
                        </g>
                    </svg>

                    --}}



                </div>
                <img class="device-mockup" src="/images/iphone-mockup-.png" alt="App preview">
                <div class="hero-app-dots hero-app-dots-1">
                    <svg width="124" height="75" xmlns="http://www.w3.org/2000/svg">
                        <g fill="none" fill-rule="evenodd">
                            <path fill="#FFF" d="M33.392 0l3.624 1.667.984 3.53-1.158 3.36L33.392 10l-3.249-1.639L28 5.196l1.62-3.674z"/>
                            <path fill="#7487A3" d="M74.696 3l1.812.833L77 5.598l-.579 1.68L74.696 8l-1.624-.82L72 5.599l.81-1.837z"/>
                            <path fill="#556B8B" d="M40.696 70l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L38 72.599l.81-1.837z"/>
                            <path fill="#7487A3" d="M4.314 37l2.899 1.334L8 41.157l-.926 2.688L4.314 45l-2.6-1.31L0 41.156l1.295-2.94zM49.314 32l2.899 1.334.787 2.823-.926 2.688L49.314 40l-2.6-1.31L45 36.156l1.295-2.94z"/>
                            <path fill="#556B8B" d="M99.696 56l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L97 58.599l.81-1.837zM112.696 37l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L110 39.599l.81-1.837zM82.696 37l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L80 39.599l.81-1.837zM122.618 57l1.087.5.295 1.059-.347 1.008-1.035.433-.975-.492-.643-.95.486-1.101z"/>
                        </g>
                    </svg>
                </div>
                <div class="hero-app-dots hero-app-dots-2">
                    <svg width="124" height="75" xmlns="http://www.w3.org/2000/svg">
                        <g fill="none" fill-rule="evenodd">
                            <path fill="#556B8B" d="M33.392 0l3.624 1.667.984 3.53-1.158 3.36L33.392 10l-3.249-1.639L28 5.196l1.62-3.674zM74.696 3l1.812.833L77 5.598l-.579 1.68L74.696 8l-1.624-.82L72 5.599l.81-1.837zM40.696 70l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L38 72.599l.81-1.837zM4.314 37l2.899 1.334L8 41.157l-.926 2.688L4.314 45l-2.6-1.31L0 41.156l1.295-2.94zM49.314 32l2.899 1.334.787 2.823-.926 2.688L49.314 40l-2.6-1.31L45 36.156l1.295-2.94z"/>
                            <path fill="#FFF" d="M99.696 56l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L97 58.599l.81-1.837z"/>
                            <path fill="#556B8B" d="M112.696 37l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L110 39.599l.81-1.837z"/>
                            <path fill="#FFF" d="M82.696 37l1.812.833.492 1.765-.579 1.68-1.725.722-1.624-.82L80 39.599l.81-1.837z"/>
                            <path fill="#556B8B" d="M122.618 57l1.087.5.295 1.059-.347 1.008-1.035.433-.975-.492-.643-.95.486-1.101z"/>
                        </g>
                    </svg>
                </div>



            </div>
        </div>
    </div>
</section>




<section class="features section">
    <div class="container">
        <div class="features-inner section-inner has-bottom-divider">
            <h2 class="section-title mt-0">Возможности</h2>
            <div class="features-wrap">
                <div class="feature is-revealing">
                    <div class="feature-inner">
                        <div class="feature-icon">
                            <svg width="64" height="64" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient x1="0%" y1="100%" x2="50%" y2="0%" id="feature-1-a">
                                        <stop stop-color="#F9425F" stop-opacity=".8" offset="0%"/>
                                        <stop stop-color="#47A1F9" stop-opacity=".16" offset="100%"/>
                                    </linearGradient>
                                    <linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="feature-1-b">
                                        <stop stop-color="#FDFFDA" offset="0%"/>
                                        <stop stop-color="#F97059" stop-opacity=".798" offset="49.935%"/>
                                        <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                                    </linearGradient>
                                </defs>
                                <g fill="none" fill-rule="evenodd">
                                    <path d="M24 48H0V24C0 10.745 10.745 0 24 0h24v24c0 13.255-10.745 24-24 24" fill="url(#feature-1-a)"/>
                                    <path d="M40 64H16V40c0-13.255 10.745-24 24-24h24v24c0 13.255-10.745 24-24 24" fill="url(#feature-1-b)"/>
                                </g>
                            </svg>
                        </div>
                        <h3 class="feature-title mt-24">Создавать и редактировать</h3>
                        <p class="text-sm mb-0">Возможно не только создать розыгрыш но и отредактировать.<br/>У опубликованного розыгрыша возможно редактировать дату завершения, описание, кол-во победителей</p>
                    </div>
                </div>
                <div class="feature is-revealing">
                    <div class="feature-inner">
                        <div class="feature-icon">
                            <svg width="68" height="64" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient x1="0%" y1="100%" x2="50%" y2="0%" id="feature-2-a">
                                        <stop stop-color="#F9425F" stop-opacity=".8" offset="0%"/>
                                        <stop stop-color="#47A1F9" stop-opacity=".16" offset="100%"/>
                                    </linearGradient>
                                    <linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="feature-2-b">
                                        <stop stop-color="#FDFFDA" offset="0%"/>
                                        <stop stop-color="#F97059" stop-opacity=".798" offset="49.935%"/>
                                        <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                                    </linearGradient>
                                </defs>
                                <g fill="none" fill-rule="evenodd">
                                    <path d="M9.941 63.941v-24c0-13.255 10.745-24 24-24h24v24c0 13.255-10.745 24-24 24h-24z" fill="url(#feature-2-a)" transform="rotate(45 33.941 39.941)"/>
                                    <path d="M16 0v24c0 13.255 10.745 24 24 24h24V24C64 10.745 53.255 0 40 0H16z" fill="url(#feature-2-b)"/>
                                </g>
                            </svg>
                        </div>
                        <h3 class="feature-title mt-24">Специальные условия</h3>
                        <p class="text-sm mb-0">Настроить специальные условия для принятия участия в розыгрыше. <br /> Проставив {число} количество приглашенных, участнику вашей группы необходимо будет пригласить такое количество участников в вашу групу чтоб принять участие в розыгрыше</p>
                    </div>
                </div>
                <div class="feature is-revealing">
                    <div class="feature-inner">
                        <div class="feature-icon">
                            <svg width="64" height="64" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient x1="50%" y1="100%" x2="50%" y2="43.901%" id="feature-3-a">
                                        <stop stop-color="#F97059" stop-opacity=".798" offset="0%"/>
                                        <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                                    </linearGradient>
                                    <linearGradient x1="58.893%" y1="100%" x2="58.893%" y2="18.531%" id="feature-3-b">
                                        <stop stop-color="#F9425F" stop-opacity=".8" offset="0%"/>
                                        <stop stop-color="#47A1F9" stop-opacity="0" offset="100%"/>
                                    </linearGradient>
                                    <linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="feature-3-c">
                                        <stop stop-color="#FDFFDA" offset="0%"/>
                                        <stop stop-color="#F97059" stop-opacity=".798" offset="49.935%"/>
                                        <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                                    </linearGradient>
                                </defs>
                                <g fill="none" fill-rule="evenodd">
                                    <path fill="url(#feature-3-a)" opacity=".32" d="M0 24h64v40H0z"/>
                                    <path fill="url(#feature-3-b)" d="M40 24H24L0 64h64z"/>
                                    <path d="M10 10v22c0 12.15 9.85 22 22 22h22V32c0-12.15-9.85-22-22-22H10z" fill="url(#feature-3-c)" transform="rotate(45 32 32)"/>
                                </g>
                            </svg>
                        </div>
                        <h3 class="feature-title mt-24">Уведомление о завершении</h3>
                        <p class="text-sm mb-0">По завершению розыгрыша, автоматически среди участников выбирается то количество победителей что вы проставите в розыгрыше. В сообщение с розыгрышем прописываются победители, и к вам приходит уведомление о завершении</p>
                    </div>
                </div>



            </div>
        </div>
    </div>
</section>



<section class="newsletter section" id="pay">
    <div class="container-sm">
        <div class="newsletter-inner section-inner">
            <div class="newsletter-header text-center">
                <h2 class="section-title mt-0">Код публикации</h2>
                <p class="section-paragraph"><span style="color: red">Внимание</span>: необходимо вводить действительный email,<br />на него придет код публикации</p>

            </div>
            <div class="footer-form newsletter-form field field-grouped text-center">

                <div class="control" style="margin: 0 auto 40px;">
                    <a class="button button-primary button-block button-shadow payyoo" href="#">Получить</a>
                </div>

            </div>
            <div class="newsletter-header text-center">
                <p class="section-paragraph" style="font-size: 12px;">Опубликовать можно без кода публикации, если количество приглашенных у розыгрыша стоит 0</p>
            </div>
        </div>
    </div>
</section>


<div class="form" style="display: none;">
    <form method="POST" class="formyoo" action="https://yoomoney.ru/quickpay/confirm.xml">
        {{csrf_field()}}
        <input type="hidden" name="receiver" value="410014133809236">
        <input type="hidden" name="formcomment" value="Получение промокода для публикации">
        <input type="hidden" name="short-dest" value="Спасибо, за вашу поддержку">
        <input type="hidden" name="label" value="{{$rand}}">
        <input type="hidden" name="quickpay-form" value="shop">
        <input type="hidden" name="targets" value="Получить промокод">
        <input type="hidden" name="sum" value="1400.00" data-type="number">
        <input type="hidden" name="comment" value="Спасибо за оплату, вернитесь на сайт для дальнейшей инструкции">
        <input type="hidden" name="need-fio" value="false">
        <input type="hidden" name="need-email" value="true">
        <input type="hidden" name="successURL" value="https://voterpro.ru/thanks">
        <input type="hidden" name="need-phone" value="false">
        <input type="hidden" name="need-address" value="false">
        <label><input type="hidden" name="paymentType" value="AC">Банковской картой</label>
        <input type="submit" value="Перевести">
    </form>
</div>


@endsection