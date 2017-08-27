<style>
    .kaart {

    }

    html {
        margin: 5px;
    }

    .img-circle {
        border-radius: 50%;
    }

</style>

    <script src="https://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>

    <script src="/js/bootstrap.min.js"></script>
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
    <script type="text/javascript" src="http://gc.kis.scr.kaspersky-labs.com/1B74BD89-2A22-4B93-B451-1C9E1052A0EC/main.js" charset="UTF-8"></script>
    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>



<div class="stad" style="margin-bottom: 10px; background-color: #74AFAD">

        <div class="col-md-2" style="border: 1px solid black;">
            <img src="{{ $stad->afbeelding }}" width="100%" class="img-circle" alt=""  />
        </div>
        <div class="col-md-10" style="border: 1px solid black;">
            <h1>Stad {{ $stad->stad }}</h1>
            <h2><b>Parkings: </b>{{ count($stad->parkings) }}<br/>
                <b>Park And Rides: </b>{{ count($stad->parkings->where('parkandride', 1)) }}<br/>
                <b>Plaatsen: </b> {{ $stad->totaal_plaatsen() }}
            </h2>
        </div>

</div>

<br/>

<div class="kaart" style="background-color: #558C89">

    <h2>Kaart</h2>
    <div id='map' style="width: 100%; height: 500px;"></div>

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

<div class="parkeerzones">
    <h2>Parkeerzones</h2>
</div>

