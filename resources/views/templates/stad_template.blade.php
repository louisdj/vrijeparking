@extends('......app')

@section('content')

    <style>
        #parking:hover {
            opacity: 0.8;
            cursor: pointer;
        }
    </style>


    <header style="margin-top:-50px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Stad {{ $stad->stad }} </h2>
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Grid Section -->
    <section id="portfolio" style="margin-top: -50px; padding-left: 20px; padding-right: 20px;">
        <div class="container">
            <div class="row" >

                @if($stad->bericht)

                    <div class="alert @if($stad->bericht_type == 0)alert-info
                                      @elseif($stad->bericht_type == 1)alert-success
                                      @else alert-warning
                                      @endif alert-dismissible fade in" role="alert">

                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button> <strong>Opgepast!</strong>

                        {{ $stad->bericht }}
                    </div>

                @endif

                <h3>Overzicht parkings</h3>

                <div class="parkings">

                    <div class="parkings_website hidden-md hidden-sm hidden-xs">

                        <?php $count = 0; ?>

                        @foreach($parkings as $parking)

                            <?php $count++ ?>

                            @if($count == 1 || $count == 6 || $count == 11)
                                <div class="row">
                            @endif

                                @if($parking->live_data == 1)
                                    <div onclick="window.location.href='/parking/{{ strtolower(addslashes($parking->naam)) }}'" id="parking" class="col-sm-2 hidden-lg-down" style="margin-right: 20px; margin-bottom: 10px; text-align: center;
                                    background-color: @if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) lightcoral;
                                                                                                      @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) orange; @endif">
                                        <h5>{{ $parking->naam }}</h5>
                                        <div class="row" style="text-align: center;">
                                            <div class="col-xs-6 col-sm-12" >
                                                <img onerror="this.src='/img/parkings/placeholder.jpg'" src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e", $parking->naam)) }}.jpg" alt="" width="100%" height="115px;" style="border-radius: 20px;"/>
                                            </div>
                                        </div>
                                        <h5>
                                            {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}
                                        </h5>
                                    </div>
                                @else
                                    <div onclick="window.location.href='/parking/{{ strtolower(addslashes($parking->naam)) }}'" id="parking" class="col-sm-2 hidden-lg-down" style="margin-right: 20px; margin-bottom: 10px; text-align: center;">
                                        <h5>{{ $parking->naam }}</h5>
                                        <div class="row" style="text-align: center;">
                                            <div class="col-xs-6 col-sm-12" >
                                                <img onerror="this.src='/img/parkings/placeholder.jpg'" src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e",$parking->naam)) }}.jpg" alt="" width="100%" height="115px;" style="border-radius: 20px;"/>
                                            </div>
                                        </div>
                                        <h5>
                                            Geen realtime
                                        </h5>
                                    </div>
                                @endif

                            @if($count == 5 || $count == 10 || $count == 15)
                                </div>
                            @endif

                        @endforeach

                    </div>


                    <div class="parkings_mobile">

                    <div class="@if($stad->live_data == 1) col-md-12  @else col-md-12 @endif visible-md visible-sm visible-xs">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                              <tr>
                                <th>Parking</th>
                                <th>Locatie</th>
                                <th>Prijs/2u</th>
                                <th>Beschikbaar</th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach($parkings as $parking)
                                    @if($parking->totaal_plaatsen != 0 && $parking->beschikbare_plaatsen != 0)
                                        <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->naam) ? strtolower(addslashes($parking->naam)) : "Niet beschikbaar" }}'" class="@if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) danger
                                                    @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) warning @endif">
                                            <td>
                                                <img height="25px" src="/img/parkings/parking-icon.gif" alt=""/>
                                                {{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar" }}
                                            </td>
                                            <td>{{ $parking->adres }}</td>
                                            <td>@if(isset($parking->starttarief)) €{{ number_format($parking->starttarief->prijs, 2, ',', ' ') }} @elseif($parking->gratis == 1) Gratis @else - @endif</td>
                                            <td>
                                                    {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->naam) ? strtolower(addslashes($parking->naam)) : "Niet beschikbaar" }}'">
                                            <td>
                                                <img height="25px" src="/img/parkings/parking-icon.gif" alt=""/>
                                                {{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar" }}
                                            </td>
                                            <td>{{ $parking->adres }}</td>
                                            <td>@if(isset($parking->starttarief)) €{{ number_format($parking->starttarief->prijs, 2, ',', ' ') }} @elseif($parking->gratis == 1) Gratis @else - @endif</td>
                                            <td>
                                                    {{--{{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}--}}
                                                    -
                                            </td>
                                        </tr>
                                    @endif

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>

                <br/><br/>

                <div class="parkandrides">

                @if(count($parkandrides) > 0)
                <h3>Park & Rides</h3>

                <?php $count = 0; ?>

                @foreach($parkandrides as $parking)

                    <?php $count++ ?>

                    @if($count == 1 || $count == 6 || $count == 11)
                        <div class="row hidden-md hidden-sm hidden-xs">
                    @endif

                        @if($parking->live_data == 1)
                            <div onclick="window.location.href='/parking/{{ strtolower(addslashes($parking->naam)) }}'" id="parking" class="col-sm-2 hidden-lg-down" style="margin-right: 20px; margin-bottom: 10px; text-align: center;
                            background-color: @if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) lightcoral;
                                                                                              @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) orange; @endif">
                                <h5>{{ $parking->naam }}</h5>
                                <div class="row" style="text-align: center;">
                                    <div class="col-xs-6 col-sm-12" >
                                        <img onerror="this.src='/img/parkings/placeholder.jpg'" src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e", $parking->naam)) }}.jpg" alt="" width="100%" height="115px;" style="border-radius: 20px;"/>
                                    </div>
                                </div>
                                <h5>
                                    {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}
                                </h5>
                            </div>
                        @else
                            <div onclick="window.location.href='/parking/{{ strtolower(addslashes($parking->naam)) }}'" id="parking" class="col-sm-2 hidden-lg-down" style="margin-right: 20px; margin-bottom: 10px; text-align: center;">
                                <h5>{{ $parking->naam }}</h5>
                                <div class="row" style="text-align: center;">
                                    <div class="col-xs-6 col-sm-12" >
                                        <img onerror="this.src='/img/parkings/placeholder.jpg'" src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e",$parking->naam)) }}.jpg" alt="" width="100%" height="115px;" style="border-radius: 20px;"/>
                                    </div>
                                </div>
                                <h5>
                                    Geen realtime
                                </h5>
                            </div>
                        @endif

                    @if($count == 5 || $count == 10 || $count == 15)
                        </div>
                    @endif

                @endforeach
                @endif

                </div>

                @if(count($parkandrides) > 0)

                <div class="@if($stad->live_data == 1) col-md-12  @else col-md-12 @endif visible-md visible-sm visible-xs">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                          <tr>
                            <th>Parking</th>
                            <th>Locatie</th>
                            <th>Prijs/2u</th>
                            <th>Beschikbaar</th>
                          </tr>
                        </thead>
                        <tbody>
                            @foreach($parkandrides as $parking)
                                @if($parking->totaal_plaatsen != 0 && $parking->beschikbare_plaatsen != 0)
                                    <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->naam) ? strtolower(addslashes($parking->naam)) : "Niet beschikbaar" }}'" class="@if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) danger
                                                @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) warning @endif">
                                        <td>
                                            <img height="25px" src="/img/parkings/parking-icon.gif" alt=""/>
                                            {{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar" }}
                                        </td>
                                        <td>{{ $parking->adres }}</td>
                                        <td>@if(isset($parking->starttarief)) €{{ number_format($parking->starttarief->prijs, 2, ',', ' ') }} @elseif($parking->gratis == 1) Gratis @else - @endif</td>
                                        <td>
                                                {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}
                                        </td>
                                    </tr>
                                @else
                                    <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->naam) ? strtolower(addslashes($parking->naam)) : "Niet beschikbaar" }}'">
                                        <td>
                                            <img height="25px" src="/img/parkings/parking-icon.gif" alt=""/>
                                            {{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar" }}
                                        </td>
                                        <td>{{ $parking->adres }}</td>
                                        <td>@if(isset($parking->starttarief)) €{{ number_format($parking->starttarief->prijs, 2, ',', ' ') }} @elseif($parking->gratis == 1) Gratis @else - @endif</td>
                                        <td>
                                                {{--{{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}--}}
                                                -
                                        </td>
                                    </tr>
                                @endif

                            @endforeach
                        </tbody>
                    </table>
                </div>

                @endif

                <h6>* Beschikbaarheid: <span style="color: #e74c3c">minder dan 10%</span>, <span style="color: #f39c12">minder dan 30%</span> </h6>


            </div>

            <div class="row">


            @if($stad->live_data == 1)
                <hr/>

                <div class="col-md-8">

                    <h3>Twitter robot</h3>
                    <img src="https://pbs.twimg.com/profile_images/689562976177778691/n2cRcEoV.png" class="img-rounded img-responsive" alt="" width="75px" style="float:left; padding-right: 7px;"/>

                    <a href="https://twitter.com/VrijeParking{{ substr($stad->stad, 0,1) }}" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @VrijeParking{{ substr($stad->stad, 0,1) }}</a><br/>
                    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

                    Aangezien stad {{ $stad->stad }} de realtime bezetting van zijn parkings ter beschikking stelt, kunnen wij zowel op onze website als op Twitter een continu reeël beeld
                    geven van de beschikbare parking. Op twitter doen wij dit aan de hand van een robot die de data ophaalt per kwartier en vervolgens hier updates over geeft.
                    Wanneer een parking minder dan 30% beschikbaarheid heeft wordt dit meegegeven. Ook wanneer de parking terug <b>meer</b> dan 30% heeft wordt dit meegedeeld.
                    Dit alles met afwisseling van <b>"Summary tweets"</b> die een opsomming geven en tenslotte nog tweets die melden als een parking compleet volzet is.

                </div>
            @endif

            @if($stad->live_data == 1)
                <div class="col-md-4" style="border-left: 1px solid lightgrey;">
                    <div id="container" style="min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto"></div>
                </div>
            @endif
        </div>

            <hr/>

            <div class="row">
                <h3>Kaart</h3>

                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
                <script type="text/javascript" src="http://gc.kis.scr.kaspersky-labs.com/1B74BD89-2A22-4B93-B451-1C9E1052A0EC/main.js" charset="UTF-8"></script><script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

                <div class="col-md-12">
                    <div id='map'></div>

                    <script>
                        var mymap = L.map('map').setView([{{ $stad->coordinaten }}],14);

                        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
                            maxZoom: 18,
                            id: 'mapbox.streets'
                        }).addTo(mymap);


                        var lokatie = L.icon({
                            iconUrl: 'http://images.clipartpanda.com/google-location-icon-whitakergroup-google-location-icon-195x300.png',

                            iconSize:     [35, 55], // size of the icon
                            iconAnchor:   [20, 55], // point of the icon which will correspond to marker's location
                            popupAnchor:  [-3, -56] // point from which the popup should open relative to the iconAnchor
                        });

                        var parkandride = L.icon({
                            iconUrl: '/img/parkings/PR.png',

                            iconSize:     [35, 55], // size of the icon
                            iconAnchor:   [20, 55], // point of the icon which will correspond to marker's location
                            popupAnchor:  [-3, -56] // point from which the popup should open relative to the iconAnchor
                        });

                        var parking = L.icon({
                            iconUrl: '/img/parkings/P.png',

                            iconSize:     [35, 55], // size of the icon
                            iconAnchor:   [20, 55], // point of the icon which will correspond to marker's location
                            popupAnchor:  [-3, -56] // point from which the popup should open relative to the iconAnchor
                        });

                        @foreach($parkandrides as $parking)

                            var marker = L.marker([{{ $parking->latitude  }}, {{ $parking->longitude }}]@if($parking->parkandride), {icon: parkandride} @else, {icon: parking} @endif)
                            .bindPopup("{{ $parking->naam }}").addTo(mymap);

                        @endforeach

                        @foreach($parkings as $parking)

                            var marker = L.marker([{{ $parking->latitude  }}, {{ $parking->longitude }}]@if($parking->parkandride), {icon: parkandride} @else, {icon: parking} @endif)
                            .bindPopup("{{ $parking->naam }}").addTo(mymap);

                        @endforeach

                    </script>

                </div>


                <div class="col-md-12">
                    {{--<div style="text-decoration:none; overflow:hidden; height:500px; width:100%; max-width:100%;"><div id="embedded-map-display" style="height:100%; width:100%;max-width:100%;">--}}
                        {{--<iframe style="height:100%;width:100%;border:0;" frameborder="0" src="https://www.google.com/maps/embed/v1/place?q=gent,+België&key=AIzaSyAN0om9mFmy1QN6Wf54tXAowK4eT0ZUPrU"></iframe>--}}
                    {{--</div><a class="code-for-google-map" rel="nofollow" href="https://www.tubeembed.com/" id="grab-maps-authorization">TubeEmbed</a><style>#embedded-map-display .text-marker{max-width:none!important;background:none!important;}img{max-width:none}</style></div><script src="https://www.tubeembed.com/google-maps-authorization.js?id=007412e5-bc86-aac6-b8f1-8c890bbf6cbc&c=code-for-google-map&u=1451183307" defer="defer" async="async"></script>--}}
                    {{--<script src="https://maps.googleapis.com/maps/api/js"></script>--}}
                    

                    {{--<div id="map"></div>--}}

                </div>
            </div>
        </div>
    </section>

    <script>
            var bezet =
                    @foreach($parkings as $parking)
                        @if(isset($parking->beschikbare_plaatsen))
                             {{ $parking->totaal_plaatsen - $parking->beschikbare_plaatsen }} +
                        @endif
                    @endforeach
              0;

            var totaal =
                    @foreach($parkings as $parking)
                        @if(isset($parking->beschikbare_plaatsen))
                          {{ $parking->totaal_plaatsen }} +
                        @endif
                    @endforeach
              0;
        </script>

        <script src="/js/chart.js"></script>

@endsection