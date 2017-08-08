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
              <div class="col-md-5 col-md-offset-3" style="padding-left: 40px;">
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
              <div class="col-md-6" style="padding-left: 40px; padding-right: 40px;">
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

            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-3 portfolio-item" style="text-align: center;">
                    <a href="/stad/gent">
                        <div class="img__wrap">
                            <img class="img__img" src="img/gent.jpg" width="257" height="200" />
                            <div class="img__description_layer">
                                <p class="img__description">Gent</p>
                          </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 portfolio-item">
                    <a href="/stad/kortrijk">
                        <div class="img__wrap">
                            <img class="img__img" src="img/kortrijk.jpg" width="257" height="200" />
                            <div class="img__description_layer">
                                <p class="img__description">Kortrijk</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3"></div>
            </div>


            <style>
            /* relevant styles */
            .img__wrap {
              position: relative;
              height: 200px;
              width: 257px;
            }

            .img__description_layer {
              position: absolute;
              top: 0;
              bottom: 0;
              left: 0;
              right: 0;
              background: rgba(36, 62, 206, 0.6);
              color: #fff;
              visibility: hidden;
              opacity: 0;
              display: flex;
              align-items: center;
              justify-content: center;

              /* transition effect. not necessary */
              transition: opacity .2s, visibility .2s;
            }

            .img__wrap:hover .img__description_layer {
              visibility: visible;
              opacity: 1;
            }

            .img__description {
              transition: .2s;
              font-size: 25px;
              transform: translateY(1em);
            }

            .img__wrap:hover .img__description {
              transform: translateY(0);
            }
            </style>


            <div class="row" style="margin-top:60px;">
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

            <link rel="stylesheet" href="https://mistic100.github.io/jQCloud/dist/jqcloud2/dist/jqcloud.min.css">
            <script src="https://mistic100.github.io/jQCloud/dist/jqcloud2/dist/jqcloud.min.js"></script>

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
