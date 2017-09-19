@extends('app', ['nofooter', 'true'])

@section('head')

 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css" />
 <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>

 <link rel="stylesheet" href="/css/leaflet-sidebar.css" />

 <script src="/js/leaflet-sidebar.js"></script>

@endsection


@section('content')

<style>
    #mapid {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 100%;
    }

    tr:hover {
        background-color: lemonchiffon;
        cursor: pointer;
    }

    tr.selected
    {
        background-color: lemonchiffon;
    }

    .sidebar-pane
    {
        padding-left: 0px;
    }
</style>


<script>
    $(document).ready(function(){
        $("tr").click(function(){
            $(this).addClass("selected").siblings().removeClass("selected");
        });

        $("img").click(function(){
            $('tr').removeClass("selected");
        });

    });

</script>



<div id="sidebar" class="sidebar" style="margin-top: 110px; z-index: 500;">
    <!-- Nav tabs -->
    <div class="sidebar-tabs" style="border-right: 1px solid grey">
        <ul role="tablist">
            <li class="@if(!isset($start)) active @endif"><a href="#home" role="tab"><i class="fa fa-bars"></i></a></li>
            <li class="@if(isset($start)) active @endif "><a href="#zoeken" role="tab"><i class="fa fa-search"></i></a></li>
            @if(count($zones) > 0)<li><a href="#zones" role="tab"><i class="fa fa-road"></i></a></li>@endif
        </ul>

        <ul role="tablist">
            <li><a href="#settings" role="tab"><i class="fa fa-gear"></i></a></li>
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="sidebar-content">
        <div class="sidebar-pane @if(!isset($start)) active @endif" id="home">
            <h1 class="sidebar-header">
                &nbsp;&nbsp; Overzicht parkings
                <span class="sidebar-close"><i class="fa fa-caret-left"></i></span>
            </h1>

            <p>
                <table style="width: 100%">

                @if(count($parkings) > 0)

                <?php $count = 0 ?>

                    @foreach($parkings as $parking)

                        <tr style="padding-right: 15px; border-bottom: 1px dotted grey;" onclick="mymap.setView([{{ $parking->latitude }}+0.0015, {{ $parking->longitude }}],17); markers[{{ $count }}].openPopup();">
                            <a href="#">
                                <td style="padding-left: 5px; padding-right: 15px; text-align: center;">
                                    <img width="70px" height="70px" style="border-radius: 50%;" src="/img/parkings/gent/{{ strtolower($parking->naam) }}.jpg" onerror="this.src='/img/parkings/parking-icon.gif'" alt=""/>

                                </td>
                                <td>
                                    <h3>{{ $parking->naam }}</h3>
                                    <h5>{{ $parking->adres }} {{ $parking->stad }}</h5>
                                    <kbd>
                                        @if($parking->live_data)
                                            {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }} beschikbaar
                                        @else
                                            Totaal {{ $parking->totaal_plaatsen }} plaatsen
                                        @endif
                                    </kbd>
                                    <span>
                                        &nbsp;&nbsp;&nbsp;{{ $parking->afstand }} meter <i class="fa fa-blind" aria-hidden="true"></i>
                                    </span>
                                    <br/><br/>
                                </td>
                            </a>
                        </tr>

                        @if($count < count($parkings))  <?php $count++ ?>  @endif
                    @endforeach

                @else

                    <div class="alert alert-warning alert-dismissible fade in" role="alert" style="margin-left: 8px;">
                        <strong>Helaas!</strong> Wij zijn nog niet op de hoogte van parkings in deze buurt. Geef ons een seintje via <b><u>Parkings@vrijeparking.be</u></b>.
                    </div>

                @endif

                </table>

            </p>

        </div>

        <div class="sidebar-pane @if(isset($start)) active @endif" id="zoeken">
            <h1 class="sidebar-header">
                 &nbsp;&nbsp; Zoeken <span class="sidebar-close"><i class="fa fa-caret-left"></i></span>
            </h1>

            <div style="padding-left: 10px; text-align:center;">
                <form action="/vindparking" id="vindParkingForm" method="post">

                      <p style="padding-top: 5px; margin-bottom: -20px;">
                            <img src="/img/parkings/parking-icon.gif" width="100px" alt=""/>
                      </p>
                      <p>
                            <h3 style="color: dodgerblue">Vind parking in Vlaanderen</h3>
                            Vertrek niet langer onvoorbereid naar uw bestemming.
                            <br/>
                      </p>
                      <p>

                            <input name="location" id="searchTextField" type="text" class="form-control" label="Zoeken..." required />
                            <input name="coordinates" type="hidden" id="coordinates"  />
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                      </p>
                      <p>
                        <button type="submit" class="btn btn-success form-control">Zoeken</button>
                        {{--<button type="button" onclick="locate()" class="btn btn-primary form-control" style="margin-top: 3px;"><i class="fa fa-location-arrow" aria-hidden="true"></i>  Zoek via locatie</button>--}}
                      </p>

                </form>
            </div>

        </div>

        @if(count($zones) > 0)
        <div class="sidebar-pane" id="zones">
            <h1 class="sidebar-header">
                &nbsp;&nbsp; Overzicht parkeerzones
                <span class="sidebar-close"><i class="fa fa-caret-left"></i></span>
            </h1>

            <p>
                <table style="width: 100%">

                    @foreach($zones as $zone)
                        <tr style="padding-right: 15px; border-bottom: 1px dotted grey;">
                            <a href="#">
                                <td style="padding-left: 10px; padding-right: 12px">
                                    <div style="width:70px; height: 70px; border-radius: 40px; background-color: {{ $zone->kleur }};"></div>
                                </td>
                                <td>
                                    <h3 style="margin-top: 2px; margin-bottom: -5px;">Zone {{ $zone->zonenummer }}</h3>
                                    <span style="font-size: 16px; font-weight: bold;"><b>{{ $zone->beschrijving }}</b></span><br/>
                                    <small>
                                        <span style="font-weight: bold;">
                                            {{ $zone->parkingduur }}
                                        </span>
                                        <span style="font-weight: bold; color: darkblue">
                                            @if($zone->parkingkost) &nbsp;&nbsp; ({{ $zone->parkingkost }}) @endif
                                        </span>
                                    </small>

                                </td>
                            </a>
                        </tr>
                    @endforeach

                </table>
            </p>
        </div>
        @endif

        <div class="sidebar-pane" id="settings">
            <h1 class="sidebar-header">&nbsp;&nbsp; Settings<span class="sidebar-close"><i class="fa fa-caret-left"></i></span></h1>

            <div style="padding-left: 10px; text-align:center;">

                <h2>Parkeerzones</h2>

                    <button type="button" id="toonPoly" disabled class="btn btn-success" onclick="this.disabled = true; document.getElementById('hidePoly').disabled = false; polygons.forEach(showpolygons)">Toon polygons</button>
                    <button type="button" id="hidePoly" class="btn btn-danger" onclick="this.disabled = true; document.getElementById('toonPoly').disabled = false; polygons.forEach(removepolygons)">Verwijder polygons</button>

                <hr/>

            </div>

        </div>
    </div>
</div>




<div id="mapid" ></div>



<script>

	var mymap = L.map('mapid').setView([{{ $lat }}, {{ $Lng }}],{{ $zoom }});

	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		id: 'mapbox.streets',
	}).addTo(mymap);

	mymap.zoomControl.setPosition('topright');

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

    @if(!isset($start))
	    L.marker([{{ $lat  }}, {{ $Lng }}], {icon: lokatie}).addTo(mymap).bindPopup('Uw locatie').openPopup();
    @endif

	var sidebar = L.control.sidebar('sidebar').addTo(mymap);
	var markers = [];

	@foreach($parkings as $parking)

        var marker = L.marker([{{ $parking->latitude  }}, {{ $parking->longitude }}]@if($parking->parkandride), {icon: parkandride} @else, {icon: parking} @endif)
        .bindPopup('<h3 style="margin-bottom: 2px;">{{ $parking->naam }}</h3>' +

        @if($parking->voorstel_vernoeming)
        '<small>Gemeld door gebruiker <b>{{ $parking->voorstel_vernoeming }}</b></small><br>' +
        @endif

        '<h5><span class="glyphicon glyphicon-pushpin" aria-hidden="true"></span>&nbsp; {{ str_replace("\n", "", $parking->adres) }}  {{ $parking->stad }}</h5>' +
        '<h5><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span>&nbsp; {{ $parking->afstand }} Meter wandelafstand</h5>' +

        @if(isset($parking->starttarief))
        '<h5><span class="glyphicon glyphicon-euro" aria-hidden="true"></span>&nbsp; {{ $parking->starttarief }} / 2 uur</h5>' +
        @elseif($parking->gratis == 1)
        '<h5><span class="glyphicon glyphicon-euro" aria-hidden="true"></span>&nbsp; Gratis</h5>' +
        @endif

        @if($parking->live_data)
            '<div id="plaatsen" class="@if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) volzet  @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) bijna_volzet @else vrij @endif">@if($parking->live_data) {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }} beschikbaar @else Niet live @endif </div>' +
        @else

        @endif

        '<img id="parking_banner" class="img-responsive" src="/img/parkings/gent/{{ strtolower($parking->naam) }}.jpg" onerror="this.style.display=\'none\'"> ' +



        '<a target="_blank" href="/parking/{{ $parking->naam }}"><input type="button" style="margin-top: 4px; width:50%" class="btn btn-primary" value="Bekijken"></a>' +
        '<input type="button" data-toggle="modal" data-target="#myModal" onclick="route({{ $parking->latitude  }}, {{ $parking->longitude }}, \'{{ str_replace("\n", "", $parking->adres) }} {{ $parking->stad }}\')" style="margin-top: 4px; margin-left: 1%; width:49%" class="btn btn-success" value="Krijg route">')
        .addTo(mymap);

	    markers.push(marker);

	@endforeach

    var polygons = [];
    var zone_id;

    @foreach($zones as $key => $zone)

        @for($i = 1; $i <= $gebieden_array[$key]; $i++)

            var myCoordinates = [
                @foreach($zone->zone_gebieden as $zone_gebied)

                    @if($zone_gebied->gebied == $i)
                        [{{ $zone_gebied->coordinaten }}],
                    @endif

                @endforeach
             ];

              var polygon = L.polygon(myCoordinates, {color: '{{ $zone->kleur }}'}).bindPopup('Zone {{ $zone->zonenummer }} {{ $zone->beschrijving }}').addTo(mymap);

            polygons.push(polygon);

        @endfor

     @endforeach


    //Nieuw startpunt kiezen
	var popup = L.popup();

    function onMapClick(e) {
        popup
             .setLatLng(e.latlng)
            .setContent(" <a href='/vindparking/"+e.latlng.lat +"," + e.latlng.lng + "'><input type='button' style='width: 100%' class='btn btn-primary' value='Zoek nabij dit punt'></a><br><input type='button' data-toggle='modal' data-target='#parking_toevoegen' onclick='toevoegen("+e.latlng.lat +"," + e.latlng.lng + ")' style='margin-top: 4px; width:100%' class='btn btn-success' value='Parking toevoegen'>")
            .openOn(mymap);
    }

    function route(lat, long, adres)
    {
        document.getElementById("googlemaps").href = "https://www.google.be/maps/dir//" + lat + "," + long;
        document.getElementById("adres").value = adres;
        document.getElementById("url").value = "https://www.google.be/maps/dir//" + lat + "," + long;
        document.getElementById("route_coordinaten").value = lat + "," + long;
    }

    function toevoegen(lat, long)
    {
        document.getElementById("toevoegen_coordinaten").value = lat + "," + long;
    }

    mymap.on('click', onMapClick);


    function removepolygons(polygon)
    {
        polygon.remove();
    }

    function showpolygons(polygon)
    {
        polygon.addTo(mymap);
    }



    var current_position, current_accuracy;

    function onLocationFound(e) {
      // if position defined, then remove the existing position marker and accuracy circle from the map
      if (current_position) {
          mymap.removeLayer(current_position);
          mymap.removeLayer(current_accuracy);
      }

      var radius = e.accuracy / 2;

      current_position = L.marker(e.latlng).addTo(mymap)
        .bindPopup("U bevind zich binnen " + radius + " meter van dit punt.").openPopup();

      current_accuracy = L.circle(e.latlng, radius).addTo(mymap);
    }

    function onLocationError(e) {
      alert(e.message);
    }

    mymap.on('locationfound', onLocationFound);
    mymap.on('locationerror', onLocationError);

    // wrap map.locate in a function
    function locate() {
      mymap.locate({setView: true, maxZoom: 16});
    }

    // call locate every 3 seconds... forever
//    setInterval(locate, 5000);

</script>

<style>
    #plaatsen {
        font-size: 20px;
        border-radius: 4px;
        text-align: center;
        color: white;
    }

    .volzet {
        background-color: #FE2E2E;
        border: 1px solid #FE2E2E;
    }

    .bijna_volzet {
        background-color: #FE9A2E;
        border: 1px solid #FE9A2E;
    }

    .vrij {
        background-color: #3c763d;
        border: 1px solid #3c763d;
    }

    #parking_banner {
        padding-top: 5px;
        padding-bottom: 5px;
        margin-right: 30px;
        width: 300px;
    }

</style>


<div style="margin-top: 15%" class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Krijg de route</h4>
      </div>
      <div class="modal-body">

            <div class="row">
                <div class="col-md-6" style="padding-right: 2px;">
                    <label for="url">Google URL:</label>
                    <input id="url" name="url" type="text" class="form-control" readonly value=""/>
                </div>
                <div class="col-md-6" style="padding-left: 2px;">
                    <label for="route_coordinaten">Coordinaten:</label>
                    <input id="route_coordinaten" name="route_coordinaten" type="text" class="form-control" readonly value=""/>
                </div>
            </div>

            <label for="adres" style="margin-top: 8px;">Adres:</label>
            <input id="adres" name="adres" type="text" class="form-control" readonly value=""/>

            <a href="" target="_blank" id="googlemaps" >
                <button class="btn btn-success form-control" style="margin-top: 8px;">
                    <span class="glyphicon glyphicon-globe" aria-hidden="true"></span> Open in Google Maps
                </button>
            </a>
      </div>

    </div>
  </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-JAW99MJVpJBGcbzEuXk4Az05s/XyDdBomFqNlM3ic+I=" crossorigin="anonymous"></script>

<script>
     // wait for the DOM to be loaded
     $(function() {
       // bind 'myForm' and provide a simple callback function
       $('#toevoeg_form').ajaxForm(function() {
           $('#bericht').html("Uw suggestie werd goed ontvangen. Wij bekijken deze spoedig!<br/>");
           document.getElementById("toevoeg_form").reset();
       });
     });
</script>

<div style="margin-top: 10%" class="modal fade" id="parking_toevoegen" tabindex="-1" role="dialog" aria-labelledby="parking_toevoegen">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Stel een parking voor</h4>
      </div>
      <div class="modal-body" id="suggestie_body">
        <div id="bericht"></div>

        <form action="/suggestie" method="post" id="toevoeg_form">
            <div class="row">
                <div class="col-md-6" style="padding-right: 2px;">
                    <label for="url">Naam parking*:</label>
                    <input id="url" name="naam" type="text" class="form-control" value="" required=""/>
                </div>
                <div class="col-md-6" style="padding-left: 2px;">
                    <label for="route_coordinaten">Coordinaten:</label>
                    <input id="toevoegen_coordinaten" name="coordinaten" type="text" class="form-control" readonly value=""/>
                </div>
            </div>

            <div class="row" style="margin-top: 8px;">
                <div class="col-md-9" style="padding-right: 2px;">
                    <label for="url">Adres:</label>
                    <input id="url" name="adres" type="text" class="form-control" value="" placeholder="Bv. Polderstraat 21"/>
                </div>
                <div class="col-md-3" style="padding-left: 2px;">
                    <label for="route_coordinaten">Stad:</label>
                    {{--<input  class="form-control" value="" placeholder="Bv. Frederik"/>--}}
                    <input type="text" class="form-control" name="stad" placeholder="Gent"/>
                </div>
            </div>

            <div class="row" style="margin-top: 8px;">
                <div class="col-md-6" style="padding-right: 2px;">
                    <label for="url">Geschatte plaatsen*:</label>
                    <input id="url" name="plaatsen" type="number" class="form-control" value="" placeholder="Bv. 30" required=""/>
                </div>
                <div class="col-md-6" style="padding-left: 2px;">
                    <label for="route_coordinaten">Uw vernoeming:</label>
                    <input id="toevoegen_coordinaten" name="vernoeming" type="text" class="form-control" value="" placeholder="Bv. Frederik"/>
                </div>
            </div>

            <a href="" target="_blank" id="googlemaps" >
                <button class="btn btn-success form-control" style="margin-top: 8px;">
                    Insturen van suggestie
                </button>
            </a>
        </form>
      </div>

    </div>
  </div>
</div>



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

@endsection