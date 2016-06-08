@extends('......app')

@section('content')

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
    <section id="portfolio">
        <div class="container">
            <div class="row">
                <h3>Overzicht parkings</h3>
                <div class="col-md-8">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                          <tr>
                            <th>Parking</th>
                            <th>Locatie</th>
                            <th>Beschikbaar</th>
                          </tr>
                        </thead>
                        <tbody>
                            @foreach($parkings as $parking)
                                @if(isset($parking->beschikbare_plaatsen))
                                    <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->naam) ? strtolower(addslashes($parking->naam)) : "Niet beschikbaar" }}'" class="@if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) danger
                                                @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) warning @endif">
                                        <td>
                                            <img height="25px" src="/img/parkings/parking-icon.gif" alt=""/>
                                            {{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar" }}
                                        </td>
                                        <td>{{ $parking->adres }}</td>
                                        <td>
                                                {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <h6>* Beschikbaarheid: <span style="color: #e74c3c">minder dan 10%</span>, <span style="color: #f39c12">minder dan 30%</span> </h6>
                </div>
                <div class="col-md-4">
                    <div id="container" style="min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto"></div>
                </div>

            </div>

                <hr/>
                <h3>Twitter robot</h3>
                <img src="https://pbs.twimg.com/profile_images/689562976177778691/n2cRcEoV.png" class="img-rounded img-responsive" alt="" width="75px" style="float:left; padding-right: 7px;"/>

                <a href="https://twitter.com/VrijeParkingK" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @VrijeParkingG</a><br/>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

                Aangezien stad {{ $stad->stad }} de realtime bezetting van zijn parkings ter beschikking stelt, kunnen wij zowel op onze website als op Twitter een continu reeël beeld
                geven van de beschikbare parking. Op twitter doen wij dit aan de hand van een robot die de data ophaalt per kwartier en vervolgens hier updates over geeft.
                Wanneer een parking minder dan 30% beschikbaarheid heeft wordt dit meegegeven. Ook wanneer de parking terug <b>meer</b> dan 30% heeft wordt dit meegedeeld.
                Dit alles met afwisseling van <b>"Summary tweets"</b> die een opsomming geven en tenslotte nog tweets die melden als een parking compleet volzet is.


            <hr/>

            <div class="row">
                <h3>Kaart</h3>
                <div class="col-md-12">
                    {{--<div style="text-decoration:none; overflow:hidden; height:500px; width:100%; max-width:100%;"><div id="embedded-map-display" style="height:100%; width:100%;max-width:100%;">--}}
                        {{--<iframe style="height:100%;width:100%;border:0;" frameborder="0" src="https://www.google.com/maps/embed/v1/place?q=gent,+België&key=AIzaSyAN0om9mFmy1QN6Wf54tXAowK4eT0ZUPrU"></iframe>--}}
                    {{--</div><a class="code-for-google-map" rel="nofollow" href="https://www.tubeembed.com/" id="grab-maps-authorization">TubeEmbed</a><style>#embedded-map-display .text-marker{max-width:none!important;background:none!important;}img{max-width:none}</style></div><script src="https://www.tubeembed.com/google-maps-authorization.js?id=007412e5-bc86-aac6-b8f1-8c890bbf6cbc&c=code-for-google-map&u=1451183307" defer="defer" async="async"></script>--}}
                    {{--<script src="https://maps.googleapis.com/maps/api/js"></script>--}}
                    <script>

                          function initialize()
                          {
                           var mapOptions = {
                             zoom: 14,
                             center: new google.maps.LatLng({{ $stad->coordinaten }})
                           }
                           var map = new google.maps.Map(document.getElementById("map"), mapOptions);

                                setMarkers(map);
                          }

                          var parkings = [
                              @foreach($parkings as $parking)
                                    ["{{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar"   }}" , {{ $parking->latitude  }}, {{ $parking->longitude  }}, "/parking/{{ isset($parking->naam) ? strtolower($parking->naam) : "Niet beschikbaar"  }}"],
                                @endforeach
                              ['Maroubra Beach', -33.950198, 151.259302, 1]
                        ];

                          var zerzer = [
                            ['Bondi Beach', -33.890542, 151.274856, 4],
                            ['Coogee Beach', -33.923036, 151.259052, 5],
                            ['Cronulla Beach', -34.028249, 151.157507, 3],
                            ['Manly Beach', -33.80010128657071, 151.28747820854187, 2],
                            ['Maroubra Beach', -33.950198, 151.259302, 1]
                          ];

                          function setMarkers(map) {
                            // Adds markers to the map.

                            // Marker sizes are expressed as a Size of X,Y where the origin of the image
                            // (0,0) is located in the top left of the image.

                            // Origins, anchor positions and coordinates of the marker increase in the X
                            // direction to the right and in the Y direction down.
                            var image = {
                              url: '/img/transpa_parkingflag.png',
                              // This marker is 20 pixels wide by 32 pixels high.
                              size: new google.maps.Size(20, 32),
                              // The origin for this image is (0, 0).
                              origin: new google.maps.Point(0, 0),
                              // The anchor for this image is the base of the flagpole at (0, 32).
                              anchor: new google.maps.Point(0, 32)
                            };
                            // Shapes define the clickable region of the icon. The type defines an HTML
                            // <area> element 'poly' which traces out a polygon as a series of X,Y points.
                            // The final coordinate closes the poly by connecting to the first coordinate.
                            var shape = {
                              coords: [1, 1, 1, 20, 18, 20, 18, 1],
                              type: 'poly'
                            };
                            for (var i = 0; i < parkings.length; i++) {
                              var parking = parkings[i];
                              var marker = new google.maps.Marker({
                                position: {lat: parking[1], lng: parking[2]},
                                map: map,
                                icon: image,
                                shape: shape,
                                title: parking[0],
                        //      zIndex: parking[3],
                                url: parking[3]
                              });

                              google.maps.event.addListener(marker, 'click', function() {
                              console.log(this.url);
                                    window.location.href = this.url;

                                });
                            }
                          }

                          google.maps.event.addDomListener(window, 'load', initialize);
                    </script>

                    <div id="map"></div>

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