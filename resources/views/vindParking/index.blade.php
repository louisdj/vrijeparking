@extends('...app')

@section('content')

    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places"></script>
    <script>
        // This example adds a search box to a map, using the Google Place Autocomplete
        // feature. People can enter geographical searches. The search box will return a
        // pick list containing a mix of places and predicted search terms.

        function initialize() {

          var markers = [];
          var map = new google.maps.Map(document.getElementById('map-canvas'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP
          });

          var defaultBounds = new google.maps.LatLngBounds(
              new google.maps.LatLng(-33.8902, 151.1759),
              new google.maps.LatLng(-33.8474, 151.2631));
          map.fitBounds(defaultBounds);

          // Create the search box and link it to the UI element.
          var input = (document.getElementById('pac-input'));
          //map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

          var searchBox = new google.maps.places.SearchBox((input));

          // [START region_getplaces]
          // Listen for the event fired when the user selects an item from the
          // pick list. Retrieve the matching places for that item.
          google.maps.event.addListener(searchBox, 'places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
              return;
            }
            for (var i = 0, marker; marker = markers[i]; i++) {
              marker.setMap(null);
            }

            // For each place, get the icon, place name, and location.
            markers = [];
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0, place; place = places[i]; i++) {
              var image = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
              };

              // Create a marker for each place.
              var marker = new google.maps.Marker({
                map: map,
                icon: image,
                title: place.name,
                position: place.geometry.location
              });

              console.log(position);

              markers.push(marker);

              bounds.extend(place.geometry.location);
            }

            map.fitBounds(bounds);
          });
          // [END region_getplaces]

          // Bias the SearchBox results towards places that are within the bounds of the
          // current map's viewport.
          google.maps.event.addListener(map, 'bounds_changed', function() {
            var bounds = map.getBounds();
            searchBox.setBounds(bounds);
          });
        }

        google.maps.event.addDomListener(window, 'load', initialize);

    </script>

    <header style="margin-top:-50px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Vind Parkeerplaats</h2>
                    {{--<hr class="star-light">--}}
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Grid Section -->
    <section id="portfolio">
        <div class="container">
            <div class="row">

                <label for="locatie">Vind parkeerplek nabij uw locatie</label>
                <input id="pac-input" class="form-control" type="text" placeholder="Search Box">

            </div>

            <hr/>

            <div class="row">
                <h3>Kaart</h3>
                <div class="col-md-12">
                    <div id="map-canvas" style="width: 100%; height:500px;"></div>
                </div>
            </div>
        </div>
    </section>


@endsection