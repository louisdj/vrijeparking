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

    <section style="background-attachment: fixed; text-shadow: 2px 2px black; color:white; height: 500px; background-image: url('/img/app/app_banner.png');" >
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

    <section id="portfolio">
        <div class="container">
            <div class="row" style="margin-top: -75px;">
                <div class="col-lg-12 text-center">
                    <img class="img-responsive" src="/img/parkingBelgie.png" alt="kaartBelgië" style="margin: 0 auto"/>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Steden</h2><br/>
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
        </div>
    </section>



@endsection