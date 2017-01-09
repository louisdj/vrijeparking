@extends('app_community')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Toevoegen</h2>
                </div>
            </div>
        </div>
    </header>


    <section id="portfolio">
        <div class="container" style="margin-top: -100px">

            <div class="row">
                <div style="width: 97%; margin: 0 auto; padding: 20px 0 40px;">

                    @if(!Auth::check())

                    <div class="alert alert-warning">
                      <strong>Opgelet!</strong> Inloggen is niet vereist maar het geeft u krediet voor uw werk.
                      <a href="/login" class="alert-link">Hier inloggen</a>.
                    </div>

                    @endif

                    @if(!empty($message))

                    <div class="alert alert-success">
                      <strong>Succes!</strong> {{ $message }}
                    </div>

                    @endif

                    <div class="tabcontents">
                        <div id="view">
                            <form action="/community/toevoegen" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <h3><b>Nieuwe parking </b><small> Deze parking zal door ons geverifieëerd worden.</small></h3>
                            <p>
                                <table class="table table-striped table-bordered table-hover text-center" >
                                    <tr class="info"  >
                                        <th class="text-center">Naam</th>
                                        <th class="text-center">Waarde</th>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:middle">Naam</td>
                                        <td style="vertical-align:middle"><input type="text" class="form-control" value="" name="naam"/></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:middle">Stad</td>
                                        <td style="vertical-align:middle">

                                            <select name="stad" class="form-control" id="sel1">
                                                <option>Gent</option>
                                                <option>Brussel</option>
                                                <option>Kortrijk</option>
                                            </select>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Locatie</td>
                                        <td>
                                            <div id="map" height="50px"></div>
                                            <script async defer src="https://maps.googleapis.com/maps/api/js?callback=initMap&sensor=false"></script>
                                                <script>
                                                    var geocoder;
                                                    var map;

                                                  function initMap() {

                                                      geocoder = new google.maps.Geocoder();
                                                      map = new google.maps.Map(document.getElementById('map'), {
                                                        center: {lat: -34.397, lng: 150.644},
                                                        zoom: 16
                                                      });
                                                      var infoWindow = new google.maps.InfoWindow({map: map});

                                                      // Try HTML5 geolocation.
                                                      if (navigator.geolocation) {
                                                        navigator.geolocation.getCurrentPosition(function(position) {
                                                          var pos = {
                                                            lat: position.coords.latitude,
                                                            lng: position.coords.longitude
                                                          };

                                                          document.getElementById('lat').value = position.coords.latitude;
                                                          document.getElementById('long').value = position.coords.longitude;

                                                          infoWindow.setPosition(pos);
                                                          infoWindow.setContent('Je kan de markering verplaatsen om preciezer te zijn.');

                                                          var marker = new google.maps.Marker({
                                                            position: pos,
                                                            map: map,
                                                            draggable: true
                                                          });

                                                          infoWindow.open(map, marker);

                                                          google.maps.event.addListener(marker, 'dragend', function() {

                                                              document.getElementById('lat').value = marker.getPosition().lat();
                                                              document.getElementById('long').value = marker.getPosition().lng();

                                                            });

                                                          map.setCenter(pos);
                                                        }, function() {
                                                          handleLocationError(true, infoWindow, map.getCenter());
                                                        });
                                                      } else {
                                                        // Browser doesn't support Geolocation
                                                        handleLocationError(false, infoWindow, map.getCenter());
                                                      }

                                                      geocoder.geocode( { 'address': "deinze"}, function(results, status)
                                                      {
                                                          if (status == google.maps.GeocoderStatus.OK) {
                                                            map.setCenter(results[0].geometry.location);
                                                            }
                                                            else {

                                                            }
                                                      });

                                                    }

                                                    function geocodePosition(pos) {

                                                      geocoder.geocode({
                                                        latLng: pos
                                                      }, function(responses) {
                                                        if (responses && responses.length > 0) {
                                                          alert(responses[0].formatted_address);
                                                        } else {
                                                          alert('Cannot determine address at this location.');
                                                        }
                                                      });
                                                    }

                                                    function handleLocationError(browserHasGeolocation, infoWindow, pos) {
                                                      infoWindow.setPosition(pos);
                                                      infoWindow.setContent(browserHasGeolocation ?
                                                                            'Error: The Geolocation service failed.' :
                                                                            'Error: Your browser doesn\'t support geolocation.');
                                                    }
                                                </script>

                                        </td>
                                    </tr>

                                    <input type="hidden" name="lat" id="lat" value=""/>
                                    <input type="hidden" name="long" id="long" value=""/>

                                    <tr>
                                        <td style="vertical-align:middle">Adres</td>
                                        <td style="vertical-align:middle"><input type="text" class="form-control" value=""  name="adres"/></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:middle">Afbeelding</td>
                                        <td style="vertical-align:middle"><input type="file" class="form-control" value="" name="afbeelding"/></td>
                                    </tr>

                                    <tr>
                                        <td style="vertical-align:middle">Omschrijving</td>
                                        <td style="vertical-align:middle"><input type="textarea" rows="5" class="form-control" value="" name="omschrijving"/></td>
                                    </tr>

                                    <tr>
                                        <td style="vertical-align:middle">Telefoon</td>
                                        <td style="vertical-align:middle"><input type="text" class="form-control" value="" name="telefoon"/></td>
                                    </tr>

                                    <tr>
                                        <td style="vertical-align:middle">Totaal # plaatsen</td>
                                        <td style="vertical-align:middle"><input type="text" class="form-control" value="" name="totaal_plaatsen"/></td>
                                    </tr>

                                </table>
                            </p>

                            {{--<h3>Betaalmiddelen</h3>--}}
                            {{--<select name="betaalmiddelen[]" multiple>--}}
                                {{--<option value="1">Maestro</option>--}}
                                {{--<option value="2">Bancontact</option>--}}
                                {{--<option value="3">Visa</option>--}}
                                {{--<option value="4">Mastercard</option>--}}
                                {{--<option value="5">cash</option>--}}
                            {{--</select>--}}

                            {{--<input type="checkbox" name="maestro" value="1"><label for="maestro">Maestro</label><br>--}}
                            {{--<input type="checkbox" name="bancontact" value="2"><label for="bancontact">Bancontact</label><br>--}}
                            {{--<input type="checkbox" name="visa" value="3"><label for="visa">visa</label><br>--}}
                            {{--<input type="checkbox" name="mastercard" value="4"><label for="mastercard">mastercard</label><br>--}}
                            {{--<input type="checkbox" name="cash" value="5"><label for="cash">cash</label>--}}

                            <br/><br/>

                             <a href=""><button class="btn-primary"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Creëer parking</button></a>
                             <a href=""><button class="btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Annuleren</button></a>
                             </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection