@extends('app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Over ons</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <p>VrijeParking is een initiatief om alle beschikbare parkingdata uit België eenvoudig en toegankelijk beschikbaar te stellen voor het grote publiek.
                        Wij breiden ons aanbod van data steeds zoveel mogelijk uit en proberen steden/bedrijven steeds aan te moedigen hun data beschikbaar te stellen.<p>
                </div>
            </div>
        </div>
    </header>

    <section style="background-attachment: fixed; background-size: 100% 100%; text-shadow: 2px 2px black; color:white; background-image: url('/img/app/app_banner.png');" >
        <div class="row">
              <div class="col-md-5 col-md-offset-3">
                    <h1>Mobiele App</h1><br>
                    <h3>
                        <ul>
                            <li>Realtime beschikbare plaatsen</li>
                            <li>Eenvoudige navigatie naar parkings</li>
                            <li>Onthoud je parkeerplaats</li>
                            <li>Vind eenvoudig parkings in jouw buurt</li>
                        </ul>
                    </h3>
                    <br/>
                    <a href="https://play.google.com/store/apps/details?id=com.ionicframework.vrijeparking374441"><img src="/img/app/android.png" width="200px" alt=""/></a>
                    <img src="/img/app/ios.png" style="opacity: 0.2" width="200px" alt=""/>
              </div>

        </div>

    </section>

    <style>
        .zoekParkings {
            background: #258cd1; /* For browsers that do not support gradients */
            background: -webkit-linear-gradient(#258cd1, white); /* For Safari 5.1 to 6.0 */
            background: -o-linear-gradient(#258cd1, white); /* For Opera 11.1 to 12.0 */
            background: -moz-linear-gradient(#258cd1, white); /* For Firefox 3.6 to 15 */
            background: linear-gradient(#258cd1, white); /* Standard syntax */
        }
    </style>

    <section style="color:white; background-color: #258cd1;" class="zoekParkings">
        <div class="row">
              <div class="col-md-5 col-md-offset-3">
                    <h1>Zoek naar parking</h1><br>
                    <div class="input-group">
                          <div class="input-group-addon"><bold>P</bold></div>
                          <input type="text" class="form-control" placeholder="Parking" id="auto" name="keywords" autocomplete="on">

                          <script>
                                  $( "#auto" ).autocomplete({
                                      source: '/autocomplete',
                                      minLength:2,
                                      select: function( event, ui ) {
                                          window.location.href = '/parking/' + ui.item.value;
                                      }
                                  });
                          </script>
                    </div>
              </div>

        </div>

    </section>

    <section id="portfolio">
        <div class="container">
            <div class="row" style="margin-top: -75px;">
                <div class="col-lg-12 text-center">

                    <h3 id="stad">&nbsp;</h3>

                    <img class="img-responsive" id="kaart" usemap="#provinciemap" src="/img/parkingBelgie.png" alt="kaartBelgië" style="margin: 0 auto"/ >

                    <map name="provinciemap">
                      <area shape ="poly" href="/stad/kortrijk" coords ="22,156,65,275,122,239,159,256,185,240,159,84" onmouseover="changeSource('west-vlaanderen')" onmouseout="changeBack()" alt="Oost-Vlaanderen" />
                      <area shape ="poly" href="/stad/gent" coords ="166,113,186,243,252,253,306,171,297,150,323,147,311,88" onmouseover="changeSource('oost-vlaanderen')" onmouseout="changeBack()" alt="West-Vlaanderen" />
                      <area shape ="poly" href="/stad/brussel" coords ="310,230,330,243,348,238,339,204,318,209" onmouseover="changeSource('brussel')" onmouseout="changeBack()" alt="Brussel" />
                    </map>

                </div>

            </div>

            <br/>

            <script>
                function changeSource(src) {
                    document.getElementById("kaart").src = '/img/' + src + '.png';
                    document.getElementById("stad").innerHTML = src;
                }

                function changeBack() {
                    document.getElementById("kaart").src = "/img/parkingBelgie.png";
                    document.getElementById("stad").innerHTML = "&nbsp";
                }
            </script>

            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Realtime Steden</h2><br/>
                </div>
            </div>

            <div class="row" text-align="center">
                <div class="col-md-4 portfolio-item">
                    <a href="/stad/gent" class="portfolio-link" data-toggle="modal">
                        <div class="caption">
                            <div class="caption-content">
                                <i class="fa fa-3x">Gent</i>
                            </div>
                        </div>
                        <img src="img/gent.jpg" alt="">
                    </a>
                </div>
                <div class="col-sm-4 portfolio-item">
                    <a href="/stad/brussel" class="portfolio-link" data-toggle="modal">
                        <div class="caption">
                            <div class="caption-content">
                                <i class="fa fa-3x">Brussel</i>
                            </div>
                        </div>
                        <img src="img/brussel.jpg" alt="">
                    </a>
                </div>
                <div class="col-sm-4 portfolio-item">
                    <a href="/stad/kortrijk" class="portfolio-link" data-toggle="modal">
                        <div class="caption">
                            <div class="caption-content">
                                <i class="fa fa-3x">Kortrijk</i>
                            </div>
                        </div>
                        <img src="img/kortrijk.jpg" alt="">
                    </a>
                </div>
            </div>


            {{--<div class="row" style="margin-top:100px;">--}}
                {{--<div class="col-lg-12 text-center">--}}
                    {{--<h2>Steden</h2><br/>--}}
                {{--</div>--}}
            {{--</div>--}}

            {{--<div class="row" text-align="center">--}}
                {{--<div class="col-md-2" style="text-align: center">--}}
                    {{--Bredene <br/>--}}
                    {{--<img width="100%" src="http://www.freepptbackgrounds.net/wp-content/uploads/2015/02/Black-City-View-PPT-Backgrounds.jpg" alt=""/>--}}
                {{--</div>--}}
                {{--<div class="col-md-2" style="text-align: center">--}}
                    {{--Bredene <br/>--}}
                    {{--<img width="100%" src="http://www.freepptbackgrounds.net/wp-content/uploads/2015/02/Black-City-View-PPT-Backgrounds.jpg" alt=""/>--}}
                {{--</div>--}}
                {{--<div class="col-md-2" style="text-align: center">--}}
                    {{--Bredene <br/>--}}
                    {{--<img width="100%" src="http://www.freepptbackgrounds.net/wp-content/uploads/2015/02/Black-City-View-PPT-Backgrounds.jpg" alt=""/>--}}
                {{--</div>--}}
                {{--<div class="col-md-2" style="text-align: center">--}}
                    {{--Bredene <br/>--}}
                    {{--<img width="100%" src="http://www.freepptbackgrounds.net/wp-content/uploads/2015/02/Black-City-View-PPT-Backgrounds.jpg" alt=""/>--}}
                {{--</div>--}}
                {{--<div class="col-md-2" style="text-align: center">--}}
                    {{--Bredene <br/>--}}
                    {{--<img width="100%" src="http://www.freepptbackgrounds.net/wp-content/uploads/2015/02/Black-City-View-PPT-Backgrounds.jpg" alt=""/>--}}
                {{--</div>--}}
                {{--<div class="col-md-2" style="text-align: center">--}}
                    {{--Bredene <br/>--}}
                    {{--<img width="100%" src="http://www.freepptbackgrounds.net/wp-content/uploads/2015/02/Black-City-View-PPT-Backgrounds.jpg" alt=""/>--}}
                {{--</div>--}}
            {{--</div>--}}

        </div>
    </section>

    {{--<section style="text-shadow: 1px 1px black; color:white; height: 280px; background-color: black;">--}}
        {{--<div class="row" text-align="center" style="margin-top: -100px">--}}
            {{--<div class="col-lg-12 text-center">--}}
                {{--<h3>Mogelijk dankzij</h3>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--<div class="row" style="text-align: center;">--}}
            {{--<div class="col-lg-12 text-center">--}}
                {{--<img src="/img/parkingboys2.png" alt=""/>--}}
                {{--<img src="http://www.wetenschapsacademieblankenberge.be/images/logo_howest.png" alt=""/>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</section>--}}

@endsection