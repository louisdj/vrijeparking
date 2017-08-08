<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>VrijeParking.be - Vind parking in België</title>

    <!-- Bootstrap Core CSS - Uses Bootswatch Flatly Theme: http://bootswatch.com/flatly/ -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/css/freelancer.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/img/favicon.ico" type="image/x-icon">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    {{-- Google map --}}
    <script src="https://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
    <script src="https://maps.googleapis.com/maps/api/js"></script>

    {{-- UI Jquery css & js--}}
    <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css" type="text/css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.js" type="text/javascript"></script>

    {{-- Google analytics --}}
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-74737576-1', 'auto');
      ga('send', 'pageview');

    </script>

    @yield('head')

    <style>
      #map {
        width: 100%;
        height: 600px;
      }
    </style>

</head>

<body id="page-top" class="index">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand hidden-md hidden-sm hidden-xs" href="/">Vrije Parking.be</a>
                <a class="navbar-brand visible-md visible-sm visible-xs" href="/">VP</a>
            </div>



            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>

                    <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            {{ trans('navbar.parkings') }} <span class="caret"></span>
                          </a>
                          <ul class="dropdown-menu columns dropdown-menu-left">
                            <li style="background-color: lightgrey"><a><b>Live data</b></a>  </li>
                            <li><a href="/stad/gent"><img src="/img/transpa_ParkingGent.png" alt="parkingGent" width="20px"/> Stad Gent</a></li>
                            {{--<li role="separator" class="divider"></li>--}}
                            <li><a href="/stad/kortrijk"><img src="/img/transpa_ParkingKortrijk.png" alt="parkingKortrijk" width="20px"/> Stad Kortrijk</a></li>

                            {{--<li role="separator" class="divider"></li>--}}

                            <li style="background-color: lightgrey;"><a><b>Andere steden</b></a>  </li>


                          </ul>
                    </li>

                    <li>
                        <a href="/mindervaliden"><i class="fa fa-wheelchair" aria-hidden="true"></i> Voorbehouden parking</a>
                    </li>

                    <li class="page-scroll">
                        <a href="/vindparking">{{ trans('navbar.findParking') }}</a>
                    </li>

                    <li class="dropdown hidden-md hidden-sm">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-th" aria-hidden="true"></span> {{ trans('navbar.extra') }} <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="/blog"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> {{ trans('navbar.blog') }}</a></li>
                            <li><a href="/team"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> {{ trans('navbar.team') }}</a></li>
                            {{--<li><a href="/community"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Community</a></li>--}}
                        </ul>
                    </li>

                    {{--<li class="dropdown">--}}
                        {{--<a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ Config::get('languages')[App::getLocale()] }} <b class="caret"></b></a>--}}
                        {{--<ul class="dropdown-menu">--}}

                        {{--{{ Session::get('locale') }}--}}
                            {{--@foreach (Config::get('languages') as $lang => $language)--}}
                                {{--@if ($lang != App::getLocale())--}}
                                    {{--<li>--}}
                                        {{--<a href="/taal/{{$lang}}">{{ $language }}</a>--}}
                                    {{--</li>--}}
                                {{--@endif--}}
                            {{--@endforeach--}}
                        {{--</ul>--}}
                    {{--</li>--}}

                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    @if(!isset($nofooter))
    <!-- Footer -->
    <footer class="text-center">
        <div class="footer-above">
            <div class="container">
                <div class="row">
                    <div class="footer-col col-md-0">

                    </div>
                    <div class="footer-col col-md-12">
                        <h3>Social media</h3>
                        <ul class="list-inline">
                            <li>
                                <a href="//facebook.com/vrijeparking" target="_blank" class="btn-social btn-outline"><i class="fa fa-fw fa-facebook"></i></a>
                            </li>
                            <li>
                                <a href="//twitter.com/vrijeparking" target="_blank" class="btn-social btn-outline"><i class="fa fa-fw fa-twitter"></i></a>
                            </li>
                        </ul>
                    </div>
                    <div class="footer-col col-md-0">

                    </div>
                </div>
            </div>
        </div>
        <div class="footer-below">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        Copyright &copy; VrijeParking.be {{ date('Y') }} <br/>
                        <a HREF="mailto:Contact@vrijeparking.be">Contact@vrijeparking.be</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @endif

    <!-- Scroll to Top Button (Only visible on small and extra-small screen sizes) -->
    <div class="scroll-top page-scroll visible-xs visible-sm">
        <a class="btn btn-primary" href="#page-top">
            <i class="fa fa-chevron-up"></i>
        </a>
    </div>


    <!-- Bootstrap Core JavaScript -->
    <script src="/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>--}}
    <script src="/js/classie.js"></script>
    <script src="/js/cbpAnimatedHeader.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="/js/freelancer.js"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    @yield('scripts')

    <!-- Test -->

</body>
</html>
