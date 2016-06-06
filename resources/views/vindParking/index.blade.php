@extends('...app')

@section('content')

    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places"></script>

    <style>
        td {
            font-size: 21px;
        }
    </style>

    <header style="margin-top:-50px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Vind Parkeerplaats</h2>
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Grid Section -->
    <section id="portfolio">
        <div class="container">
            <div class="row">
                <form action="/vindparking" name="searchform" method="post">
                    <label for="locatie">Vind parkeerplek nabij uw adres</label><br>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-8" style="margin-right:-20px; margin-bottom: 10px;">
                                    <input id="locationTextField" name="location" class="form-control" type="text" placeholder="{{ isset($searchTerm) ? $searchTerm : "Geef uw locatie in" }}" value="">
                                    <input id="coordinates" name="coordinates" type="hidden" value=""/>
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                </div>
                                <div class="col-sm-2" style="margin-right:-20px; min-width: 200px;">
                                    <input type="submit" class="btn btn-primary" value="Zoek parking" style="width: 100%; margin-bottom:10px;" />
                                </div>
                                <div class="col-sm-2" style="margin-right:-20px; min-width: 200px">
                                    <button type="button" onclick="getLocation()" class="btn btn-primary" style="width: 100%" >
                                        <span class="glyphicon glyphicon-screenshot" aria-hidden="true"></span> Zoek nabij locatie
                                    </button>
                                </div>
                            </div>
                            <div class="row" style="padding-top: 10px;">

                            </div>
                        </div>
                </form>
            </div>

            <script>
                function init() {
                    var input = document.getElementById('locationTextField');
                    var autocomplete = new google.maps.places.Autocomplete(input);

                    google.maps.event.addListener(autocomplete, 'place_changed',
                       function() {
                          var place = autocomplete.getPlace();
                          var lat = place.geometry.location.lat();
                          var lng = place.geometry.location.lng();
                          document.getElementById("coordinates").value = lat+","+lng;
                       }
                    );
                }

                google.maps.event.addDomListener(window, 'load', init);
            </script>

            <script>
                function getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(showPosition);
                    } else {
                        alert("Geolocation is not supported by this browser.");
                    }
                }

                function showPosition(position) {
                    document.getElementById("coordinates").value = position.coords.latitude+","+position.coords.longitude;
                    document.searchform.submit();
                }
            </script>

            <hr/>

            @if(isset($parkings) && count($parkings) > 0)
                <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center" >
                    <tr class="info"  >
                        <th class="text-center">Foto</th>
                        <th class="text-center">Naam</th>
                        <th class="text-center">Adres</th>
                        <th class="text-center">Beschikbaar</th>
                        <th class="text-center">Wandelafstand</th>
                        <th class="text-center">Link</th>
                    </tr>
                    @foreach($parkings as $parking)
                    <tr class="@if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) danger
                                @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) warning @endif">
                        <td style="vertical-align:middle"><img src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e", $parking->naam)) }}.jpg" alt="" width="150px" height="100px"/></td>
                        <td style="vertical-align:middle">{{ $parking->naam }}</td>
                        <td style="vertical-align:middle">{{ $parking->adres }}</td>
                        <td style="vertical-align:middle">{{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}</td>
                        <td style="vertical-align:middle">


                        <?php

                            $distance = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $mapCenter . "&destinations=". $parking->latitude . "," . $parking->longitude . "&mode=walking&language=nl-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo"));
                            $distanceResult = ($distance->rows[0]->elements[0]);

                            echo $distanceResult->distance->text;
                            echo "<br><small>" . $distanceResult->duration->text . "</small>";

                        ?>

                        </td>
                        <td style="vertical-align:middle">
                            <a href="/parking/{{ $parking->naam }}">
                                <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </table>
                </div>
            @endif

            @if(isset($parkings) && count($parkings) == 0)
                <div class="alert alert-warning">
                 <strong>0 resultaten!</strong> Wij hebben geen openbare parkings in de buurt gevonden.

                 {{ session('emptySearch') }}
               </div>
            @endif

            <div class="row">
                <h3>Kaart</h3>
                <div class="col-md-12">
                    <script>
                      function initialize()
                      {

                       var mapOptions = {
                         zoom: {{ $zoom }},
                         center: new google.maps.LatLng({{ isset($mapCenter) ? $mapCenter : ""  }})
                       }
                       var map = new google.maps.Map(document.getElementById("map"), mapOptions);

                        @if(isset($parkings))
                        var marker = new google.maps.Marker({
                          position: new google.maps.LatLng({{ $mapCenter }}),
                          map: map,
                          title: "{{ isset($searchTerm) ? $searchTerm : "" }}"
                        });
                        @endif

                        var input = document.getElementById('pac-input');
                          var searchBox = new google.maps.places.SearchBox(input);
                          map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);


                        marker.addListener('click', function() {
                          infowindow.open(map, marker);
                        });

                        setMarkers(map);
                      }

                      var parkings = [
                      @if(isset($parkings))
                              @foreach($parkings as $parking)
                                  ["{{ $parking->naam  }}" , {{ $parking->latitude }}, {{ $parking->longitude  }}, "/parking/{{ $parking->naam }}"],
                              @endforeach
                      @endif
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
                        }
                      }

                      google.maps.event.addDomListener(window, 'load', initialize);
                </script>

                <div id="map"></div>
                </div>
            </div>
        </div>
    </section>


@endsection