<!DOCTYPE html>
<html lang="ru" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" sizes="57x57" href="/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/images/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/images/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/images/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">


    <title>VoterPro.ru бот для создания розыгрышей в вашем чате</title>
    <link href="https://fonts.googleapis.com/css?family=Heebo:400,500,700|Playfair+Display:700" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('/css/style.min.css') }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="{{ URL::asset('/js/scrollreveal-min.js') }}"></script>
</head>
<body class="is-boxed has-animations">
<div class="body-wrap boxed-container">
    <header class="site-header">
        <div class="container">
            <div class="site-header-inner">
                <div class="brand header-brand">
                    {{--
                    <h1 class="m-0">
                        <a href="">
                            <!--svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                <title>VoterPro.ru бот для создания розыгрышей в вашем чате</title>
                                <defs>
                                    <linearGradient x1="0%" y1="100%" x2="50%" y2="0%" id="logo-a">
                                        <stop stop-color="#F9425F" stop-opacity=".8" offset="0%"/>
                                        <stop stop-color="#47A1F9" stop-opacity=".16" offset="100%"/>
                                    </linearGradient>
                                    <linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="logo-b">
                                        <stop stop-color="#FDFFDA" offset="0%"/>
                                        <stop stop-color="#F97059" stop-opacity=".798" offset="49.935%"/>
                                        <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                                    </linearGradient>
                                </defs>
                                <g fill="none" fill-rule="evenodd">
                                    <path d="M22 19.22c6.627 0 9.593-6.415 9.593-13.042C31.593-.45 28.627.007 22 .007S10 2.683 10 9.31c0 6.628 5.373 9.91 12 9.91z" fill="url(#logo-a)"/>
                                    <path d="M13.666 31.889c7.547 0 10.924-7.307 10.924-14.854 0-7.547-3.377-7.027-10.924-7.027C6.118 10.008 0 13.055 0 20.603c0 7.547 6.118 11.286 13.666 11.286z" fill="url(#logo-b)" transform="matrix(-1 0 0 1 24.59 0)"/>
                                </g>
                            </svg-->
                        </a>
                    </h1>

                    --}}
                </div>
            </div>
        </div>
    </header>

    <main>


        @yield('content')






{{--
        <section class="media section">
            <div class="container-sm">
                <div class="media-inner section-inner">
                    <div class="media-header text-center">
                        <h2 class="section-title mt-0">Meet Laurel</h2>
                        <p class="section-paragraph mb-0">Lorem ipsum is common placeholder text used to demonstrate the graphic elements of a document or visual presentation.</p>
                    </div>
                    <div class="media-canvas">
                        <svg width="800" height="450" viewBox="0 0 800 450" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient x1="100%" y1="0%" x2="0%" y2="100%" id="media-canvas">
                                    <stop stop-color="#06101F" offset="0%"/>
                                    <stop stop-color="#1D304B" offset="100%"/>
                                </linearGradient>
                            </defs>
                            <rect width="800" height="450" rx="8" fill="url(#media-canvas)" fill-rule="evenodd"/>
                        </svg>
                        <div class="media-control">
                            <svg width="96" height="96" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient x1="87.565%" y1="15.873%" x2="17.086%" y2="80.538%" id="media-control">
                                        <stop stop-color="#FFF" stop-opacity=".64" offset="0%"/>
                                        <stop stop-color="#FFF" offset="100%"/>
                                    </linearGradient>
                                    <filter x="-500%" y="-500%" width="1000%" height="1000%" filterUnits="objectBoundingBox" id="media-shadow">
                                        <feOffset dy="16" in="SourceAlpha" result="shadowOffsetOuter"></feOffset>
                                        <feGaussianBlur stdDeviation="24" in="shadowOffsetOuter" result="shadowBlurOuter"></feGaussianBlur>
                                        <feColorMatrix values="0 0 0 0 0.024 0 0 0 0 0.064 0 0 0 0 0.12 0 0 0 0.24 0" in="shadowBlurOuter"></feColorMatrix>
                                    </filter>
                                </defs>
                                <g fill="none" fill-rule="evenodd">
                                    <circle fill="#FFF" cx="48" cy="48" r="48" style="mix-blend-mode:multiply;filter:url(#media-shadow)"/>
                                    <circle fill="url(#media-control)" cx="48" cy="48" r="48"/>
                                    <path d="M44.6 39.2a1.001 1.001 0 0 0-1.6.8v18a1.001 1.001 0 0 0 1.6.8l12-9a.998.998 0 0 0 0-1.6l-12-9z" fill="#1D304B"/>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        --}}




    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="site-footer-inner has-top-divider">
                <div class="brand footer-brand">
                    {{--

                    <a href="#">
                        <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                            <title>Laurel</title>
                            <defs>
                                <linearGradient x1="0%" y1="100%" x2="50%" y2="0%" id="logo-footer-a">
                                    <stop stop-color="#F9425F" stop-opacity=".8" offset="0%"/>
                                    <stop stop-color="#47A1F9" stop-opacity=".16" offset="100%"/>
                                </linearGradient>
                                <linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="logo-footer-b">
                                    <stop stop-color="#FDFFDA" offset="0%"/>
                                    <stop stop-color="#F97059" stop-opacity=".798" offset="49.935%"/>
                                    <stop stop-color="#F9425F" stop-opacity="0" offset="100%"/>
                                </linearGradient>
                            </defs>
                            <g fill="none" fill-rule="evenodd">
                                <path d="M22 19.22c6.627 0 9.593-6.415 9.593-13.042C31.593-.45 28.627.007 22 .007S10 2.683 10 9.31c0 6.628 5.373 9.91 12 9.91z" fill="url(#logo-footer-a)"/>
                                <path d="M13.666 31.889c7.547 0 10.924-7.307 10.924-14.854 0-7.547-3.377-7.027-10.924-7.027C6.118 10.008 0 13.055 0 20.603c0 7.547 6.118 11.286 13.666 11.286z" fill="url(#logo-footer-b)" transform="matrix(-1 0 0 1 24.59 0)"/>
                            </g>
                        </svg>
                    </a>

                    --}}
                </div>

                {{--

                <ul class="footer-links list-reset">
                    <li>
                        <a href="#">Contact</a>
                    </li>
                    <li>
                        <a href="#">About us</a>
                    </li>
                    <li>
                        <a href="#">FAQ's</a>
                    </li>
                    <li>
                        <a href="#">Support</a>
                    </li>
                </ul>

                --}}

                <p>Связь с автором <a href="https://t.me/shveppi">@shveppi</a></p>


                {{--

                <ul class="footer-social-links list-reset">
                    <li>
                        <a href="#">
                            <span class="screen-reader-text">Facebook</span>
                            <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.023 16L6 9H3V6h3V4c0-2.7 1.672-4 4.08-4 1.153 0 2.144.086 2.433.124v2.821h-1.67c-1.31 0-1.563.623-1.563 1.536V6H13l-1 3H9.28v7H6.023z" fill="#FFF"/>
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <span class="screen-reader-text">Twitter</span>
                            <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 3c-.6.3-1.2.4-1.9.5.7-.4 1.2-1 1.4-1.8-.6.4-1.3.6-2.1.8-.6-.6-1.5-1-2.4-1-1.7 0-3.2 1.5-3.2 3.3 0 .3 0 .5.1.7-2.7-.1-5.2-1.4-6.8-3.4-.3.5-.4 1-.4 1.7 0 1.1.6 2.1 1.5 2.7-.5 0-1-.2-1.5-.4C.7 7.7 1.8 9 3.3 9.3c-.3.1-.6.1-.9.1-.2 0-.4 0-.6-.1.4 1.3 1.6 2.3 3.1 2.3-1.1.9-2.5 1.4-4.1 1.4H0c1.5.9 3.2 1.5 5 1.5 6 0 9.3-5 9.3-9.3v-.4C15 4.3 15.6 3.7 16 3z" fill="#FFF"/>
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <span class="screen-reader-text">Google</span>
                            <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.9 7v2.4H12c-.2 1-1.2 3-4 3-2.4 0-4.3-2-4.3-4.4 0-2.4 2-4.4 4.3-4.4 1.4 0 2.3.6 2.8 1.1l1.9-1.8C11.5 1.7 9.9 1 8 1 4.1 1 1 4.1 1 8s3.1 7 7 7c4 0 6.7-2.8 6.7-6.8 0-.5 0-.8-.1-1.2H7.9z" fill="#FFF"/>
                            </svg>
                        </a>
                    </li>
                </ul>
                <div class="footer-copyright">&copy; 2021</div>

                --}}


            </div>
        </div>
    </footer>
</div>

<script src="{{ URL::asset('/js/main-min.js') }}"></script>




</body>
</html>
