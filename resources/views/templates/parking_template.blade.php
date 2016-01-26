@extends('......app')

@section('content')

    <header style="margin-top:-50px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Parking @yield('parkingTitle')</h2>
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Grid Section -->
    <section id="portfolio">
        <div class="container">


            <div class="row">
                {{--<h3>Details</h3>--}}
                <div class="col-md-4">
                    @yield('parkingFoto')
                </div>
                <div class="col-md-4">
                    <h4>Address</h4>
                    {{ $parking->address }}
                    <br/><br/>
                    <h4>Contact</h4>
                    {{ $parking->contactInfo }}
                </div>
                <div class="col-md-2">
                    <h4>Beschikbaar</h4>
                    @yield('beschikbaarheid')
                </div>
                <div class="col-md-2" style="border: 1px solid black; padding:10px; text-align:center">
                    <h4>Openingsuren</h4>
                    @yield('openingsUren')
                </div>
            </div>

            <hr/>

            <div class="row">
                <h3>Kaart</h3>
                <div class="col-md-12">
                    <script>

                      function initialize()
                      {
                           var mapOptions = {
                             zoom: 15,
                             center: new google.maps.LatLng(@yield('parkingLocatie'))
                           };

                           var map = new google.maps.Map(document.getElementById("map"), mapOptions);

                           var contentString = '<div id="content">'+
                                 '<div id="siteNotice">'+
                                 '</div>'+
                                 '<h3 id="firstHeading" class="firstHeading">'+
                                 @yield('parkingNaam')+
                                 '</h3>'+
                                 '</div>';

                             var infowindow = new google.maps.InfoWindow({
                               content: contentString
                             });

                             var marker = new google.maps.Marker({
                               position: new google.maps.LatLng(@yield('parkingLocatie')),
                               map: map,
                               title: @yield("parkingNaam")
                             });

                             infowindow.open(map, marker);

                             marker.addListener('click', function() {
                               infowindow.open(map, marker);
                             });
                      }
                      google.maps.event.addDomListener(window, 'load', initialize);
                    </script>

                    <div id="map"></div>
                </div>
            </div>
        </div>
    </section>

@endsection