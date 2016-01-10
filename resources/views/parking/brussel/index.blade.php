@extends('......app')

@section('content')

    <header style="margin-top:-50px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Parking {{ $parking->name_nl }} (Brussel)</h2>
                    {{--<hr class="star-light">--}}
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Grid Section -->
    <section id="portfolio">
        <div class="container">


            <div class="row">
                <div class="col-md-4">
                    <img src="/img/parkings/Brussel/{{$parking->name_nl}}.jpg" alt="" width="330px" height="220px" style="border-radius: 20px;"/>
                </div>
                <div class="col-md-4">
                    <h4>Address</h4>
                    {{ $parking->address_nl }}
                    <br/><br/>
                    <h4>Contact</h4>
                    {{--{{ $parking->contactInfo }}--}}
                </div>
                <div class="col-md-2">
                    <h4>Availability</h4>
                    <div class="progress" style="height:20px; vertical-align: bottom;">
                      <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ 100 - round(($parking->free_places  /  $parking->total_places) * 100) }}%; font-size:20px; padding-top: 4px;">
                        {{ 100 - round(($parking->free_places  /  $parking->total_places) * 100) }}%
                      </div>
                    </div>

                    {{--<span class="label @if(($parking->parkingStatus->availableCapacity / $parking->parkingStatus->totalCapacity) > 0.90) label-danger--}}
                                {{--@elseif(($parking->parkingStatus->availableCapacity / $parking->parkingStatus->totalCapacity) > 0.70) label-warning--}}
                                {{--@else label-default--}}
                                {{--@endif">--}}
                                {{--{{ $parking->parkingStatus->availableCapacity }} /  {{ $parking->parkingStatus->totalCapacity }} plaatsen bezet--}}
                    {{--</span>--}}
                </div>
                <div class="col-md-2" style="border: 1px solid black; padding:10px; text-align:center">
                    <h4>Opening times</h4>

                    {{--@foreach($parking->openingTimes[0]->days as $day)--}}
                        {{--{{ $day }} <br/>--}}
                    {{--@endforeach--}}
                    <hr/>
                    {{--<b>{{ $parking->openingTimesInfo->text }}</b>--}}
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
                             center: new google.maps.LatLng("{{ $parking->latitude }}", "{{ $parking->longitude  }}")
                           };

                           var map = new google.maps.Map(document.getElementById("map"), mapOptions);

                           var contentString = '<div id="content">'+
                                 '<div id="siteNotice">'+
                                 '</div>'+
                                 '<h3 id="firstHeading" class="firstHeading">{{ $parking->name_nl }} (Brussel)</h3>'+
                                 '</div>';

                             var infowindow = new google.maps.InfoWindow({
                               content: contentString
                             });

                             var marker = new google.maps.Marker({
                               position: new google.maps.LatLng("{{ $parking->latitude }}", "{{ $parking->longitude  }}"),
                               map: map,
                               title: '{{ $parking->name_nl }} (Brussel)'
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