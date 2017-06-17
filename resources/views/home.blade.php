@extends('app')

@section('content')

    {{--<header>--}}
        {{--<div class="container" style="padding-top:110px">--}}
            {{--<div class="row">--}}
                {{--<div class="col-lg-12 text-center">--}}
                    {{--<h2>Over ons</h2>--}}
                {{--</div>--}}
            {{--</div>--}}
            {{--<div class="row">--}}
                {{--<div class="col-lg-6 col-lg-offset-3">--}}
                    {{--<p>VrijeParking is een initiatief om alle beschikbare parkingdata uit België eenvoudig en toegankelijk beschikbaar te stellen voor het grote publiek.--}}
                        {{--Wij breiden ons aanbod van data steeds zoveel mogelijk uit en proberen steden/bedrijven steeds aan te moedigen hun data beschikbaar te stellen.<p>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</header>--}}

    <section style="background-attachment: fixed; background-size: 100% 100%; text-shadow: 2px 2px black; color:white; background-image: url('/img/parkings/parking-banner.jpg'); margin-top:5%;" >
        <div class="row">
              <div class="col-md-5 col-md-offset-3">
                    <h1>Mobiele App</h1><br>
                    <h3>
                        <ul>
                            <li>Realtime beschikbare plaatsen</li>
                            <li>Eenvoudige navigatie naar parkings</li>
                            <li>Assistentie bij betalen voor straatparking</li>
                            <li>Vind parkings in jouw buurt</li>
                        </ul>
                    </h3>
                    <br/>
                    <a href="https://play.google.com/store/apps/details?id=com.ionicframework.vrijeparking374441"><img src="/img/app/android.png" width="200px" alt=""/></a>
                    <a href="https://itunes.apple.com/nl/app/vrijeparking/id1175461339?mt=8"><img src="/img/app/ios.png" width="200px" alt=""/></a>
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

    <section style="color:white; background-color: #258cd1; text-align: center;" class="zoekParkings">
        <div class="row">

        <h1>Zoek naar parking in ...</h1><br>

              <div class="col-md-3"></div>
              <div class="col-md-6">
                    <form action="/vindparking" id="vindParkingForm" method="post">
                        <div class="input-group">

                              <div class="input-group-addon"><bold><span class="glyphicon glyphicon-search" aria-hidden="true"></span></bold></div>

                              <input name="location" id="searchTextField" type="text" class="form-control" label="Zoeken..." required />
                              <input name="coordinates" type="hidden" id="coordinates"  />
                              <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                        </div>
                    </form>
              </div>
              <div class="col-md-3"></div>

        </div>

    </section>

    <script>
        function initMap()
        {
            var input = document.getElementById('searchTextField');
            var autocomplete = new google.maps.places.Autocomplete(input);

            google.maps.event.addListener(autocomplete, 'place_changed',
               function() {
                  var place = autocomplete.getPlace();
                  var lat = place.geometry.location.lat();
                  var lng = place.geometry.location.lng();
                  document.getElementById("coordinates").value = lat+","+lng;
                  document.getElementById("vindParkingForm").submit();
               }
            );
        }

    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdcNxd6g8V0tyzJK87vZjsRYlnPI7DLRw&libraries=places&callback=initMap" async defer></script>


    <section id="portfolio">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Live bezetting</h2><br/>
                </div>
            </div>

            <div class="row" text-align="center">
                <div class="col-md-2"></div>
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
                {{--<div class="col-sm-4 portfolio-item">--}}
                    {{--<a href="/stad/brussel" class="portfolio-link" data-toggle="modal">--}}
                        {{--<div class="caption">--}}
                            {{--<div class="caption-content">--}}
                                {{--<i class="fa fa-3x">Brussel</i>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<img src="img/brussel.jpg" alt="">--}}
                    {{--</a>--}}
                {{--</div>--}}
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


            <div class="row" style="margin-top:100px;">
                <div class="col-lg-12 text-center">
                    <h3>Andere Steden</h3><br/>
                </div>
            </div>

            <style>
                a:hover {
                    opacity: 0.7;
                }
                #keywords {
                  width:95%;
                  height: 500px;
                  left: 2%;
                }
            </style>

            <link rel="stylesheet" href="http://mistic100.github.io/jQCloud/dist/jqcloud2/dist/jqcloud.min.css">
            <script src="http://mistic100.github.io/jQCloud/dist/jqcloud2/dist/jqcloud.min.js"></script>

            <script>
            $(document).ready(function(){
                var words = [

                    @foreach($offline_steden as $stad)
                        @if($stad->aantal_parkings() >= 2)
                            {text: "{{ $stad->stad }}", weight: {{ $stad->aantal_parkings()  }}, link: '/stad/{{ $stad->stad }}' },
                        @endif
                    @endforeach

                ];

                $('#keywords').jQCloud(words, {
                   autoResize: true
                 });
            });

            </script>


        </div>

        <div id="keywords"></div>

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