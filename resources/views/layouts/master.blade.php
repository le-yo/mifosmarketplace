<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Mifos Apps Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Corona App Kenya | Dashboard" name="description" />
    <meta content="Corona App Kenya | Dashboard" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="{{ asset('frogetor/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frogetor/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frogetor/css/style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frogetor/css/custom.css') }}" rel="stylesheet" type="text/css" />

</head>

<body>

<!-- Top Bar Start -->
<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">

        <!-- LOGO -->
        <div class="topbar-left">
            <a href="index.html" class="logo coronappheader">
                        <span>
                        Mifos Apps Marketplace
                        </span>
                <span>
                        </span>
            </a>
        </div>

        <ul class="list-unstyled topbar-nav float-right mb-0">

            {{--<li class="dropdown">--}}
                {{--<a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"--}}
                   {{--aria-haspopup="false" aria-expanded="false">--}}
                    {{--<i class="mdi mdi-bell-outline nav-icon"></i>--}}
                    {{--<span class="badge badge-danger badge-pill noti-icon-badge">2</span>--}}
                {{--</a>--}}
                {{--<div class="dropdown-menu dropdown-menu-right dropdown-lg">--}}
                    {{--<!-- item-->--}}
                    {{--<h6 class="dropdown-item-text">--}}
                        {{--Notifications (0)--}}
                    {{--</h6>--}}
                    {{--<div class="slimscroll notification-list">--}}
                        {{--<!-- item-->--}}
                        {{--<a href="javascript:void(0);" class="dropdown-item notify-item">--}}
                            {{--<div class="notify-icon bg-warning"><i class="mdi mdi-message"></i></div>--}}
                            {{--<p class="notify-details">No notifications<small class="text-muted">No new alerts</small></p>--}}
                        {{--</a>--}}
                    {{--</div>--}}
                    {{--<!-- All-->--}}
                    {{--<a href="javascript:void(0);" class="dropdown-item text-center text-primary">--}}
                        {{--View all <i class="fi-arrow-right"></i>--}}
                    {{--</a>--}}
                {{--</div>--}}
            {{--</li>--}}
            <li class="menu-item">
                <!-- Mobile menu toggle-->
                <a class="navbar-toggle nav-link" id="mobileToggle">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>
        </ul>

    </nav>
    <!-- end navbar-->
</div>
<!-- Top Bar End -->
<div class="page-wrapper-img">
    <div class="page-wrapper-img-inner">
        {{--<div class="col-lg-9 p-0 d-flex justify-content-center">--}}
            {{--<div class="accountbg d-flex align-items-center">--}}
            {{--<div class="accountbg2 d-flex media align-items-center">--}}
                {{--<div class="account-title text-white text-center">--}}
                    {{--<img src="assets/images/logo-sm.png" alt="" class="thumb-sm">--}}
                    {{--<h4 class="mt-3">Welcome To Frogetor</h4>--}}
                    {{--<div class="border w-25 mx-auto border-primary"></div>--}}
                    {{--<h1 class="">Let's Get Started</h1>--}}
                    {{--<p class="font-14 mt-3">Don't have an account ? <a href="" class="text-primary">Sign up</a></p>--}}

                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        <div class="sidebar-user media">
        {{--<div class="col-lg-9 p-0 d-flex justify-content-center">--}}
            <h1 class="page-title mb-2 coronappcolor"><i class="mdi mdi-monitor-dashboard mr-2 coronappcolor"></i>Apps to automate your Mifos Instance</h1>
            <h2 class="page-content coronappcolor">Good morning ! Have a nice day.</h2>
        </div>
        <!-- Page-Title -->
        {{--<div class="row">--}}
            {{--<div class="col-sm-12">--}}
                {{--<div class="page-title-box">--}}
                {{--</div><!--end page title box-->--}}
            {{--</div><!--end col-->--}}
        {{--</div><!--end row-->--}}
        <!-- end page title end breadcrumb -->
    </div><!--end page-wrapper-img-inner-->
</div><!--end page-wrapper-img-->

<div class="page-wrapper">
    <div class="page-wrapper-inner">

        {{--<!-- Navbar Custom Menu -->--}}
        {{--<div class="navbar-custom-menu">--}}

            {{--<div class="container-fluid">--}}
                {{--<div id="navigation">--}}
                    {{--<!-- Navigation Menu-->--}}
                    {{--<ul class="navigation-menu list-unstyled">--}}

                        {{--<li class="has-submenu">--}}
                            {{--<a href="/">--}}
                                {{--<i class="mdi mdi-monitor"></i>--}}
                                {{--Home--}}
                            {{--</a>--}}
                        {{--</li>--}}
                        {{--<li class="has-submenu">--}}
                            {{--<a href="#">--}}
                                {{--<i class="mdi mdi-monitor"></i>--}}
                                {{--Dashboard--}}
                            {{--</a>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                    {{--<!-- End navigation menu -->--}}
                {{--</div> <!-- end navigation -->--}}
            {{--</div> <!-- end container-fluid -->--}}
        {{--</div>--}}
        <!-- end left-sidenav-->
    </div>
    <!--end page-wrapper-inner -->
    <!-- Page Content-->
    @yield('content');
    <!-- end page content -->
</div>
<!-- end page-wrapper -->

<!-- jQuery  -->
<script src="{{ asset('frogetor/js/jquery.min.js') }}"></script>
<script src="{{ asset('frogetor/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('frogetor/js/waves.min.js') }}"></script>
<script src="{{ asset('frogetor/js/jquery.slimscroll.min.js') }}"></script>

<script src="{{ asset('frogetor/plugins/moment/moment.js') }}"></script>
<script src="{{ asset('frogetor/plugins/apexcharts/apexcharts.min.js') }}"></script>
<script src="https://apexcharts.com/samples/assets/irregular-data-series.js') }}"></script>
<script src="https://apexcharts.com/samples/assets/series1000.js') }}"></script>
<script src="https://apexcharts.com/samples/assets/ohlc.js') }}"></script>

<script src="{{ asset('frogetor/pages/jquery.dashboard-3.init.js') }}"></script>
{{--<script src="{{ asset('frogetor/pages/jquery.apexcharts.init.js') }}"></script>--}}

<!-- App js -->
<script src="{{ asset('frogetor/js/app.js') }}"></script>
<script src="{{ asset('frogetor/pages/jquery.dashboard.init.js') }}"></script>
@stack('scripts')
</body>
</html>
