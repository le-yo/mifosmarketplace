<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Mifos Apps Marketplace">
    <meta name="author" content="Leonard | Mobidev">
    <title>Mifos Apps Marketplace</title>

    <!-- Favicons-->
    <link rel="shortcut icon" href="{{ asset('udema') }}/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" type="image/x-icon" href="{{ asset('udema') }}/img/apple-touch-icon-57x57-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href="{{ asset('udema') }}/img/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="{{ asset('udema') }}/img/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="{{ asset('udema') }}/img/apple-touch-icon-144x144-precomposed.png">

    <!-- GOOGLE WEB FONT -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800" rel="stylesheet">

    <!-- BASE CSS -->
    <link href="{{ asset('udema') }}/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('udema') }}/css/style.css" rel="stylesheet">
    <link href="{{ asset('udema') }}/css/vendors.css" rel="stylesheet">
    <link href="{{ asset('udema') }}/css/icon_fonts/css/all_icons.min.css" rel="stylesheet">

    <!-- YOUR CUSTOM CSS -->
    <link href="{{ asset('udema') }}/css/custom.css" rel="stylesheet">

    <!-- MODERNIZR SLIDER -->
    <script src="{{ asset('udema') }}/js/modernizr_slider.js"></script>

</head>

<body>

<div id="page">

    <header class="header menu_2">
        <div id="preloader"><div data-loader="circle-side"></div></div><!-- /Preload -->
        <div id="logo">
            <a href="index.html" class="mifosmobilogo"><img src="{{ asset('udema') }}/img/phone.svg" width="49" height="42" data-retina="true" alt="">Mifos Apps Marketplace</a>
        </div>
        <ul id="top_menu">
            {{--<li><a href="login.html" class="login">Login</a></li>--}}
            {{--<li><a href="#0" class="search-overlay-menu-btn">Search</a></li>--}}
            <li class="hidden_tablet"><a href="admission.html" class="btn_1 rounded">Sign Up</a></li>
            <li class="hidden_tablet"><a href="admission.html" class="btn_1 rounded">Sign Up</a></li>
        </ul>
        <!-- /top_menu -->
        <a href="#menu" class="btn_mobile">
            <div class="hamburger hamburger--spin" id="hamburger">
                <div class="hamburger-box">
                    <div class="hamburger-inner"></div>
                </div>
            </div>
        </a>
        <nav id="menu" class="main-menu">
        </nav>
        <!-- Search Menu -->
        {{--<div class="search-overlay-menu">--}}
            {{--<span class="search-overlay-close"><span class="closebt"><i class="ti-close"></i></span></span>--}}
            {{--<form role="search" id="searchform" method="get">--}}
                {{--<input value="" name="q" type="search" placeholder="Search..." />--}}
                {{--<button type="submit"><i class="icon_search"></i>--}}
                {{--</button>--}}
            {{--</form>--}}
        {{--</div><!-- End Search Menu -->--}}
    </header>
    <!-- /header -->

    <main>
        <section class="slider">
            <div id="slider" class="flexslider">
                <ul class="slides">
                    <li>
                        <img src="{{ asset('udema') }}/img/home.jpg" alt="">
                        <div class="meta">
                            <h3>Apps to automate your Mifos Instance</h3>
                            <div class="info">
                                <p><strong>Supercharge</strong> your mifos deployment<strong></strong></p>
                            </div>
                            <a href="course-detail.html" class="btn_1">Get Started</a>
                        </div>
                    </li>
                    <li>
                        <img src="{{ asset('udema') }}/img/home.jpg" alt="">
                        <div class="meta">
                            <h3>Apps to automate your Mifos Instance</h3>
                            <div class="info">
                                <p><strong>Supercharge</strong> your mifos deployment<strong></strong></p>
                            </div>
                            <a href="course-detail.html" class="btn_1">Read more</a>
                        </div>
                    </li>
                </ul>
                <div id="icon_drag_mobile"></div>
            </div>
            {{--<div id="carousel_slider_wp">--}}
                {{--<div id="carousel_slider" class="flexslider">--}}
                    {{--<ul class="slides">--}}
                        {{--<li>--}}
                            {{--<img class="slider_app_icon" src="{{ asset('udema') }}/img/apps/kyc.png" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Onboarding Apps<span>USSD | Android | Web | IPRS</span></h3>--}}
                                {{--<small>$75 <em>$275</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/apps/scoring.png" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Credit Scoring Apps <span>Web</span></h3>--}}
                                {{--<small>$75 <em>$275</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/apps/application.png" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Loan Application <span>USSD | Android | Web</span></h3>--}}
                                {{--<small>$85 <em>$320</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/apps/disburse.png" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Loan Disbursement <span>M-PESA | Wallet</span></h3>--}}
                                {{--<small>$85 <em>$320</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/apps/repayment.png" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Repayment<span>MPESA</span></h3>--}}
                                {{--<small>$85 <em>$320</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/flex_slides/slide_2_thumb.jpg" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Reminders<span>SMS | Emails</span></h3>--}}
                                {{--<small>$85 <em>$320</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/flex_slides/slide_2_thumb.jpg" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Notifications<span>Advanced</span></h3>--}}
                                {{--<small>$85 <em>$320</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/flex_slides/slide_2_thumb.jpg" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Loan Recovery<span></span></h3>--}}
                                {{--<small>$85 <em>$320</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                        {{--<li>--}}
                            {{--<img src="{{ asset('udema') }}/img/flex_slides/slide_3_thumb.jpg" alt="">--}}
                            {{--<div class="caption">--}}
                                {{--<h3>Repayment <span>Advanced</span></h3>--}}
                                {{--<small>$55 <em>$150</em></small>--}}
                            {{--</div>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                {{--</div>--}}
            {{--</div>--}}
        </section>
        <!-- /slider -->

        <div class="container-fluid margin_120_0">
            <div class="main_title_2">
                <span><em></em></span>
                <h2>Popular Apps</h2>
                <p>Some of the popular apps that seemlessly integrate to your Mifos Instance.</p>
            </div>
            <div id="reccomended" class="owl-carousel owl-theme">
                <div class="item">
                    <div class="box_grid">
                        <figure>
                            <a href="#" class=""></a>
                            <a href="#">
                                <div class="preview"><span>More info...</span></div><img src="{{ asset('udema') }}/img/apps/disburse.png" class="img-fluid" alt=""></a>
                            {{--<div class="price">5000/- per monthly*</div>--}}

                        </figure>
                        <div class="wrapper">
                            <small>Category:Payments</small>
                            <h3>Automated disbursement via M-PESA</h3>
                            <p>This app enables automated disbursement via M-PESA as soon as disbursement is done on mifos.</p>
                            {{--<div class="rating"><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star"></i><i class="icon_star"></i> <small>(145)</small></div>--}}
                        </div>
                        <ul>
                            <li><i class="icon_clock_alt"></i> 3 days integration</li>
                            {{--<li><i class="icon_like"></i> 890</li>--}}
                            <li><a href="#">Sign up</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /item -->
                <div class="item">
                    <div class="box_grid">
                        <figure>
                            <a href="0" class=""></a>
                            <a href="#"><img src="{{ asset('udema') }}/img/apps/repayment.png" class="img-fluid" alt=""></a>
                            {{--<div class="price">$45</div>--}}
                            <div class="preview"><span>More info..</span></div>
                        </figure>
                        <div class="wrapper">
                            <small>Category:Payments</small>
                            <h3>M-PESA Repayments</h3>
                            <p>This app enables automated posting of payments from your M-PESA Paybill to Mifos.</p>
                            {{--<div class="rating"><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star"></i><i class="icon_star"></i> <small>(145)</small></div>--}}
                        </div>
                        <ul>
                            <li><i class="icon_clock_alt"></i> 10 minutes integration</li>
                            {{--<li><i class="icon_like"></i> 890</li>--}}
                            <li><a href="#">Sign up</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /item -->
                <div class="item">
                    <div class="box_grid">
                        <figure>
                            <a href="0" class=""></a>
                            <a href="#"><img src="{{ asset('udema') }}/img/apps/application.png" class="img-fluid" alt=""></a>
                            {{--<div class="price">$45</div>--}}
                            <div class="preview"><span>More info..</span></div>
                        </figure>
                        <div class="wrapper">
                            <small>Category:Enrollment</small>
                            <h3>USSD Enrollment</h3>
                            <p>This app enables your clients to enroll and apply for loans via USSD. You have the option of shared or dedicated USSD</p>
                            {{--<div class="rating"><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star"></i><i class="icon_star"></i> <small>(145)</small></div>--}}
                        </div>
                        <ul>
                            <li><i class="icon_clock_alt"></i> 1 day</li>
                            {{--<li><i class="icon_like"></i> 890</li>--}}
                            <li><a href="#">Sign up</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /item -->
                <div class="item">
                    <div class="box_grid">
                        <figure>
                            <a href="0" class=""></a>
                            <a href="#"><img src="{{ asset('udema') }}/img/apps/kyc.png" class="img-fluid" alt=""></a>
                            {{--<div class="price">$45</div>--}}
                            <div class="preview"><span>More info..</span></div>
                        </figure>
                        <div class="wrapper">
                            <small>Category:KYC</small>
                            <h3>IPRS - Know Your Customer</h3>
                            <p>This app enables you integrate KYC verification/Check to your clients</p>
                            {{--<div class="rating"><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star"></i><i class="icon_star"></i> <small>(145)</small></div>--}}
                        </div>
                        <ul>
                            <li><i class="icon_clock_alt"></i>1 day</li>
                            {{--<li><i class="icon_like"></i> 890</li>--}}
                            <li><a href="#">Sign up</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /item -->
                <div class="item">
                    <div class="box_grid">
                        <figure>
                            <a href="0" class=""></a>
                            <a href="#"><img src="{{ asset('udema') }}/img/apps/scoring.png" class="img-fluid" alt=""></a>
                            {{--<div class="price">$45</div>--}}
                            <div class="preview"><span>More info..</span></div>
                        </figure>
                        <div class="wrapper">
                            <small>Category:Credit Scoring</small>
                            <h3>Credit Scoring</h3>
                            <p>This app enables a simplified scoring method for your clients. Uses data from CRB</p>
                            {{--<div class="rating"><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star voted"></i><i class="icon_star"></i><i class="icon_star"></i> <small>(145)</small></div>--}}
                        </div>
                        <ul>
                            <li><i class="icon_clock_alt"></i>1 day</li>
                            {{--<li><i class="icon_like"></i> 890</li>--}}
                            <li><a href="#">Sign up</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /item -->
            </div>
            <!-- /carousel -->
            <div class="container">
                <p class="btn_home_align"><a href="#" class="btn_1 rounded">View all courses</a></p>
            </div>
            <!-- /container -->
            <hr>
        </div>
        <!-- /container -->
    </main>
    <!-- /main -->

    <footer>
        <div class="container margin_120_95">
            <div class="row">
                <div class="col-lg-5 col-md-12 p-r-5">
                    <p>Mifos Mobi Marketplace</p>
                    <p>Mifos Mobi enables you to to integrate your mifos seemlessly to several apps.</p>
                    <div class="follow_us">
                        <ul>
                            <li>Follow us</li>
                            <li><a href="#"><i class="ti-facebook"></i></a></li>
                            <li><a href="#"><i class="ti-twitter-alt"></i></a></li>
                            <li><a href="#"><i class="ti-google"></i></a></li>
                            <li><a href="#"><i class="ti-pinterest"></i></a></li>
                            <li><a href="#"><i class="ti-instagram"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 ml-lg-auto">
                    <h5>Useful links</h5>
                    <ul class="links">
                        <li><a href="#">Apps</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Login</a></li>
                        <li><a href="#">Register</a></li>
                        <li><a href="#">Requests</a></li>
                        <li><a href="#">Contacts</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contact with Us</h5>
                    <ul class="contacts">
                        {{--<li><a href="tel://254728355429"><i class="ti-mobile"></i> +254728355429</a></li>--}}
                        <li><a href="mailto:info@mifos.mobi"><i class="ti-email"></i> info@mifos.mobi</a></li>
                    </ul>
                    <div id="newsletter">
                        <h6>Subscribe</h6>
                        <div id="message-newsletter"></div>
                        <form method="post" action="#" name="newsletter_form" id="newsletter_form">
                            <div class="form-group">
                                <input type="email" name="email_newsletter" id="email_newsletter" class="form-control" placeholder="Your email">
                                <input type="submit" value="Submit" id="submit-newsletter">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--/row-->
            <hr>
            <div class="row">
                <div class="col-md-8">
                    <ul id="additional_links">
                        <li><a href="#0">Terms and conditions</a></li>
                        <li><a href="#0">Privacy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <div id="copy">© 2020 Mifos Mobi</div>
                </div>
            </div>
        </div>
    </footer>
    <!--/footer-->
</div>
<!-- page -->

<!-- COMMON SCRIPTS -->
<script src="{{ asset('udema') }}/js/jquery-2.2.4.min.js"></script>
<script src="{{ asset('udema') }}/js/common_scripts.js"></script>
<script src="{{ asset('udema') }}/js/main.js"></script>
<script src="{{ asset('udema') }}/assets/validate.js"></script>

<!-- FlexSlider -->
<script defer src="{{ asset('udema') }}/js/jquery.flexslider.js"></script>
<script>
    $(window).load(function() {
        'use strict';
        $('#carousel_slider').flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: false,
            slideshow: false,
            itemWidth: 280,
            itemMargin: 25,
            asNavFor: '#slider'
        });
        $('#carousel_slider ul.slides li').on('mouseover', function() {
            $(this).trigger('click');
        });
        $('#slider').flexslider({
            animation: "fade",
            controlNav: false,
            animationLoop: false,
            slideshow: false,
            sync: "#carousel_slider",
            start: function(slider) {
                $('body').removeClass('loading');
            }
        });
    });
</script>

</body>
</html>