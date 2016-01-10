@extends('app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Over VrijeParking</h2>
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

    <section id="portfolio">
        <div class="container">
            <div class="row" style="margin-top: -100px;">
                <div class="col-lg-12 text-center">
                    <img src="/img/parkingBelgie.png" alt="kaartBelgië"/>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Steden</h2><br/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 portfolio-item">
                    <a href="/stad/gent" class="portfolio-link" data-toggle="modal">
                        <div class="caption">
                            <div class="caption-content">
                                <i class="fa fa-3x">Gent</i>
                            </div>
                        </div>
                        <img src="http://www.tcbi.be/system/medias/trip_gallery_medias/620/original/teambuilding-gent-brugge-1.jpg" alt="">
                    </a>
                </div>
                <div class="col-sm-4 portfolio-item">
                    <a href="/stad/brussel" class="portfolio-link" data-toggle="modal">
                        <div class="caption">
                            <div class="caption-content">
                                <i class="fa fa-3x">Brussel</i>
                            </div>
                        </div>
                        <img src="http://www.odysse.nl/wp-content/uploads/2014/01/brussel.jpg" alt="">
                    </a>
                </div>
                <div class="col-sm-4 portfolio-item">
                    <a href="/stad/kortrijk" class="portfolio-link" data-toggle="modal">
                        <div class="caption" >
                            <div class="caption-content">
                                <i class="fa fa-3x">Kortrijk</i>
                            </div>
                        </div>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/61/KortrijkBroeltorens.JPG" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection