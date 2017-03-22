@extends('app', ['nofooter', 'true'])

@section('head')

 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css" />
 <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>

 <link rel="stylesheet" href="/css/leaflet-sidebar.css" />

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


<div id="sidebar" class="sidebar collapsed" style="margin-top: 110px; z-index: 500;">
    <!-- Nav tabs -->
    <div class="sidebar-tabs" style="border-right: 1px solid grey">
        <ul role="tablist">
            <li><a href="#home" role="tab"><i class="fa fa-bars"></i></a></li>
            <li><a href="#zoeken" role="tab"><i class="fa fa-search"></i></a></li>
        </ul>

        <ul role="tablist">
            <li><a href="#settings" role="tab"><i class="fa fa-gear"></i></a></li>
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="sidebar-content">
        <div class="sidebar-pane" id="home">
            <h1 class="sidebar-header">
                &nbsp;&nbsp; Overzicht parkings
                <span class="sidebar-close"><i class="fa fa-caret-left"></i></span>
            </h1>

            <p>

                <table style="width: 100%">

                @if(count($mindervalidenplaatsen) > 0)

                <?php $count = 0 ?>

                    @foreach($mindervalidenplaatsen as $parking)

                        <tr style="padding-right: 15px; border-bottom: 1px dotted grey;" onclick="mymap.setView([{{ $parking->latitude }}+0.0015, {{ $parking->longitude }}],17); markers[{{ $count }}].openPopup();">
                            <a href="#">
                                <td style="padding-left: 5px; padding-right: 15px;">
                                    <img width="70px" height="70px" style="border-radius: 40px;" src="@if($parking->URL_PICTURE_MAIN != "") {{ $parking->URL_PICTURE_MAIN }} @else /img/parkings/mindervaliden/mindervalide_icoon.png @endif" alt=""/>
                                </td>
                                <td>
                                    <h3>{{ $parking->ADRES_STRAAT }} {{ $parking->ADRES_NR }} {{ $parking->GEMEENTE }}</h3>
                                    <h5><b>Op {{ $parking->afstand }} meter wandelafstand</b></h5>
                                </td>
                            </a>
                        </tr>

                        @if($count < count($mindervalidenplaatsen))  <?php $count++ ?>  @endif
                    @endforeach

                @else

                    <div class="alert alert-warning alert-dismissible fade in" role="alert" style="margin-left: 8px;">
                        <strong>Helaas!</strong> Wij zijn nog niet op de hoogte van parkings in deze buurt. Geef ons een seintje via <b><u>Mindervalidenparking@vrijeparking.be</u></b>.
                    </div>

                @endif

                </table>

            </p>

        </div>

        <div class="sidebar-pane" id="zoeken">
            <h1 class="sidebar-header">
                 &nbsp;&nbsp; Zoeken <span class="sidebar-close"><i class="fa fa-caret-left"></i></span>
            </h1>

            <div style="padding-left: 10px; text-align:center;">
                <form action="/mindervaliden" method="post">

                      <p style="padding-top: 5px; margin-bottom: -20px;">
                            <img src="/img/parkings/mindervaliden/mindervalide_icoon.png" width="100px" alt=""/>
                      </p>
                      <p>
                            <h3 style="color: dodgerblue">Vind parking voor mindervaliden</h3>
                            Vertrek niet langer onvoorbereid naar uw bestemming.
                            <br/>
                      </p>
                      <p>

                            <input name="location" id="searchTextField" type="text" class="form-control" label="Zoeken..."/>
                            <input name="coordinates" type="hidden" id="coordinates"  />
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                      </p>
                      <p>
                        <button type="submit" class="btn btn-success form-control">Zoeken</button>
                      </p>

                </form>
            </div>

        </div>

        <div class="sidebar-pane" id="settings">
            <h1 class="sidebar-header">Settings<span class="sidebar-close"><i class="fa fa-caret-left"></i></span></h1>
        </div>
    </div>
</div>



<div id="mapid" class="sidebar-map"></div>


<script src="/js/leaflet-sidebar.js"></script>

<script>

	var mymap = L.map('mapid').setView([{{ $lat }}, {{ $Lng }}], 17);

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

	L.marker([{{ $lat  }}, {{ $Lng }}], {icon: lokatie}).addTo(mymap).bindPopup('Uw locatie').openPopup();

	var sidebar = L.control.sidebar('sidebar').addTo(mymap);
	var markers = [];

	@foreach($mindervalidenplaatsen as $parking)
	    @if($parking->PARKING_BREEDTE_DATA != 0)

            var marker = L.marker([{{ $parking->latitude  }}, {{ $parking->longitude }}])
            .bindPopup('<h5 style="margin-bottom: 2px;">{{ $parking->ADRES_STRAAT }} {{ $parking->ADRES_NR }} {{ $parking->GEMEENTE }}</h5>' +
            '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <b><u>Breedte:</u> </b> {{ $parking->PARKING_BREEDTE_DATA }}cm<br>' +
             '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <b><u>Lengte:</u> </b> {{ $parking->PARKING_LENGTE_DATA }}cm<br> ' +
                '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <b><u>Ondergrond</u></b>: {{ $parking->PARKING_ONDERGROND_MATERIAAL }}<br>' +
                '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <b><u>Orientatie</u></b>:' +
                 ' {{ $parking->PARKING_ORIENTATIE }}'
                 +
                '<img style="margin-top: 5px;" width="100%" src="@if($parking->URL_PICTURE_MAIN != "") {{ $parking->URL_PICTURE_MAIN }} @endif" alt=""/>' +
                '<input type="button" data-toggle="modal" data-target="#myModal" onclick="route({{ $parking->latitude  }}, {{ $parking->longitude }}, \'{{ $parking->ADRES_STRAAT }} {{ $parking->ADRES_NR }} {{ $parking->GEMEENTE }}\')" style="margin-top: 4px; width:100%" class="btn btn-success" value="Krijg route">')
            .addTo(mymap);

        @else
            var marker = L.marker([{{ $parking->latitude  }}, {{ $parking->longitude }}])
            .bindPopup('<h5 style="margin-bottom: 2px;">{{ $parking->ADRES_STRAAT }} {{ $parking->ADRES_NR }} {{ $parking->GEMEENTE }}</h5>' +
            '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> <b>Geen details</b><br>' +
                '<input type="button" data-toggle="modal" data-target="#myModal" onclick="route({{ $parking->latitude  }}, {{ $parking->longitude }}, \'{{ $parking->ADRES_STRAAT }} {{ $parking->ADRES_NR }} {{ $parking->GEMEENTE }}\')" style="margin-top: 4px; width:100%" class="btn btn-success" value="Krijg route">')
            .addTo(mymap);
        @endif

	    markers.push(marker);

	@endforeach

    //Nieuw startpunt kiezen
	var popup = L.popup();

    function onMapClick(e) {
        popup
             .setLatLng(e.latlng)
            .setContent("Zoek nabij <a href='/mindervaliden/"+e.latlng.lat +"," + e.latlng.lng + "'>dit punt</a>")
            .openOn(mymap);
    }

    function route(lat, long, adres)
    {
        document.getElementById("googlemaps").href = "https://www.google.be/maps/dir//" + lat + "," + long;
        document.getElementById("adres").value = adres;
        document.getElementById("url").value = "https://www.google.be/maps/dir//" + lat + "," + long;
        document.getElementById("route_coordinaten").value = lat + "," + long;
    }

    mymap.on('click', onMapClick);

</script>



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
           }
        );
    }

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdcNxd6g8V0tyzJK87vZjsRYlnPI7DLRw&libraries=places&callback=initMap" async defer></script>

@endsection