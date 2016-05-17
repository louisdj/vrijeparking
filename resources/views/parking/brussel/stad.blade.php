@extends('../templates/stad_template')

@section('stadsNaam')
    {{ $stad }}
@endsection


@section('parkingLijst')
    @foreach($data->Brussels as $parking)
            <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ strtolower(addslashes($parking->name_nl)) }}'">
                <td>
                    <img height="25px" src="http://www.downtownseattle.com/assets/2013/07/parking-icon.gif" alt=""/>
                    {{ $parking->name_nl }}
                </td>
                <td>{{ $parking->address_nl }}</td>
                <td>
                        Geen data
                </td>
            </tr>
    @endforeach
@endsection



@section('beschikbaarheid')
    <img src="https://www.b-europe.com/~/media/ImagesNew/Bestemmingen/Brussel/ImageCarrousels/Small/500x338-bruxelles-nl.ashx" alt="" width="375px"/>
@endsection



@section('twitter')

    <hr/>
    <h3>Twitter robot</h3>
    <img src="https://pbs.twimg.com/profile_images/689562976177778691/n2cRcEoV.png" class="img-rounded img-responsive" alt="" width="75px" style="float:left; padding-right: 7px;"/>

    <a href="https://twitter.com/VrijeParkingK" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @VrijeParkingG</a><br/>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

    Aangezien stad Kortrijk de realtime bezetting van zijn parkings ter beschikking stelt, kunnen wij zowel op onze website als op Twitter een continu reeël beeld
    geven van de beschikbare parking. Op twitter doen wij dit aan de hand van een robot die de data ophaalt per kwartier en vervolgens hier updates over geeft.
    Wanneer een parking minder dan 30% beschikbaarheid heeft wordt dit meegegeven. Ook wanneer de parking terug <b>meer</b> dan 30% heeft wordt dit meegedeeld.
    Dit alles met afwisseling van <b>"Summary tweets"</b> die een opsomming geven en tenslotte nog tweets die melden als een parking compleet volzet is.

@endsection




@section('parkingsOpKaartLijst')
    @foreach($data->Brussels as $parking)
        ["{{ $parking->name_nl  }}" , {{ $parking->latitude }}, {{ $parking->longitude  }}, "/parking/{{ strtolower($parking->name_nl) }}"],
    @endforeach
@endsection


@section('centraleMapCoordinaten')
    50.8474244,4.3584517
@endsection



@section('parkingsOpKaartMarkers')
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
@endsection


@section('bezetting')
    @foreach($data->Brussels as $parking)
        {{ $parking->free_places }} +
    @endforeach
@endsection

@section('totaal')
    @foreach($data->Brussels as $parking)
        {{ $parking->total_places }} +
    @endforeach
@endsection

@section('scripts')
    <script src="/js/chart.js"></script>
@endsection