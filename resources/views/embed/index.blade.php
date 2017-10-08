
<!DOCTYPE html>
<html>
<head>
<style>/* Stylesheet 1: */
body {
    font: 100% Lucida Sans, Verdana;
    margin: 20px;
    line-height: 26px;
}

.container {
    xmin-width: 900px;
}

.wrapper {
    position: relative;
    overflow: auto;
}

#top, #sidebar, #bottom, .menuitem {
    border-radius: 4px;
    margin: 4px;
}

#top {
    background-color: #4CAF50;
    color: #ffffff;
    padding: 15px;
}

#menubar {
    width: 200px;
    float: left
}

#main {
    padding: 10px;
    margin: 0 210px;
}

#sidebar {
    background-color: #32a4e7;
    color: #ffffff;
    padding: 10px;
    width: 180px;
    bottom: 0;
    top: 0;
    right: 0;
    position: absolute;
}

#bottom {
    border: 1px solid #d4d4d4;
    background-color: #f1f1f1;
    text-align: center;
    padding: 10px;
    font-size: 70%;
    line-height: 14px;
}

#top h1, #top p, #menulist {
    margin: 0;
    padding: 0;
}

.menuitem {
    background-color: #f1f1f1;
    border: 1px solid #d4d4d4;
    list-style-type: none;
    padding: 2px;
    cursor: pointer;
}

.menuitem:hover {
    background-color: #ffffff;
}

.menuitem:first-child {
    background-color:#4CAF50;
    color: white;
    font-weight:bold;
}

a {
    color: #000000;
    text-decoration: underline;
}

a:hover {
    text-decoration: none;
}


@media (max-width: 1100px) {
    #sidebar {
        width: auto;
        position: relative;
    }
    #main {
        margin-right: 0;
    }

}

@media (max-width: 1100px) {
    #menubar {
        width: auto;
        float: none;
    }
    #main {
        margin: 0;
    }
}
</style>

<style>/* Stylesheet 2: */

</style>

<style>/* Stylesheet 3: */

</style>
<style>/* Stylesheet 4: */



</style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
    <script type="text/javascript" src="http://gc.kis.scr.kaspersky-labs.com/1B74BD89-2A22-4B93-B451-1C9E1052A0EC/main.js" charset="UTF-8"></script>
    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

    <link rel="stylesheet" href="/css/leaflet-sidebar.css" />
    <script src="/js/leaflet-sidebar.js"></script>

</head>
<body>

<div class="container wrapper">
    <div id="top">
        <h1>Stad {{ $stad->stad }}</h1>
        <p>Op deze website vind u alles over ons parkeerbeleid</p>
    </div>
    <div class="wrapper">
        <div id="menubar">
            <ul id="menulist">
                <a href="#parkings"><li class="menuitem" onclick="reStyle(0)">Parkings</a>
                <li class="menuitem" onclick="reStyle(1)">Parkeerzones
                <li class="menuitem" onclick="reStyle(2)">Bewonerskaarten
                <li class="menuitem" onclick="reStyle(3)">Parkeerkaarten
            </ul>
        </div>
        <div id="main">
            <h1>Beste Inwoner of bezoeker</h1>
            <p>In deze tool geven wij u op een duidelijke manier weer waar, wanneer of hoelang u kan parkeren bij uw bezoek aan de stad.
            Bewoners vinden hier ook allerhande zaken zoals parkeerkaarten terug om hun eigen mobiliteit te verbeteren. Onze stad heet u alvast hartelijk welkom. <br/>
            <i>- Schepen van mobiliteit Pieter De Bruyne</i><br>

            </p>
            <h2 id="parkings">Parkings</h2>
            <p>

                <div id='map' style="width: 100%; height: 600px;"></div>

                <script>
                    var mymap = L.map('map').setView([{{ $stad->coordinaten }}],15);

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

                </script>
            </p>

            <h2>Parkeerzones</h2>
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
        <div id="sidebar">
            <h3>Informatie</h3>
            <p>
                <b>Parkings: </b>{{ count($stad->parkings) }}<br/>
                <b>Park And Rides: </b>{{ count($stad->parkings->where('parkandride', 1)) }}<br/>
                <b>Plaatsen: </b> {{ $stad->totaal_plaatsen() }}

            </p>

            <h3>Nieuws</h3>
            <p>
                Parking Brielpoort is door werken niet beschikbaar tot juni 2018.
            </p>
        </div>
    </div>


    <div id="bottom">
        Het beheer van parkings en parkeerzones van stad {{ $stad->stad }} wordt mogelijk gemaakt via het platform van © <a href="https://www.vrijeparking.be">Vrijeparking</a>.
    </div>
</div>

<script>
    function noStyles() {
        document.styleSheets[0].disabled = true;
        document.styleSheets[1].disabled = true;
        document.styleSheets[2].disabled = true;
        document.styleSheets[3].disabled = true;
    }

    function reStyle(n) {
        noStyles()
        document.styleSheets[n].disabled = false;
    }

    function closeBlackdiv() {
        var blackdiv, stylediv;
        blackdiv = document.getElementById("blackdiv")
        blackdiv.parentNode.removeChild(blackdiv);
        stylediv = document.getElementById("stylediv")
        stylediv.parentNode.removeChild(stylediv);
    }

    function showStyle(n) {
        var div, text, blackdiv;
        blackdiv = document.createElement("DIV");
        blackdiv.setAttribute("style","background-color:#000000;position:absolute;width:100%;height:100%;top:0;opacity:0.5;margin-left:-20px;");
        blackdiv.setAttribute("id","blackdiv");
        blackdiv.setAttribute("onclick","closeBlackdiv()");
        document.body.appendChild(blackdiv);
        div = document.createElement("DIV");
        div.setAttribute("id","stylediv");
        div.setAttribute("style","background-color:#ffffff;padding-left:5px;position:absolute;width:auto;height:auto;top:100px;bottom:50px;left:200px;right:200px;overflow:auto;font-family: monospace; white-space: pre;line-height:16px;");
        text = document.createTextNode(document.getElementsByTagName("STYLE")[n].innerHTML);
        div.appendChild(text);
        document.body.appendChild(div);
//alert(document.getElementsByTagName("STYLE")[n].innerHTML);
    }
    reStyle(0);
</script>
</body>
</html>

