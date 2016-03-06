@extends('../templates/stad_template')

@section('stadsNaam')
    {{ $stad }}
@endsection


@section('parkingLijst')
    @foreach($data as $parking)
        @if(isset($parking->parkingStatus->availableCapacity))
            <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->description) ? strtolower(addslashes($parking->description)) : "Niet beschikbaar" }}'" class="@if(($parking->parkingStatus->availableCapacity / $parking->parkingStatus->totalCapacity) < 0.10) danger
                        @elseif(($parking->parkingStatus->availableCapacity / $parking->parkingStatus->totalCapacity) < 0.30) warning @endif">
                <td>
                    <img height="25px" src="http://www.downtownseattle.com/assets/2013/07/parking-icon.gif" alt=""/>
                    {{ isset($parking->description) ? $parking->description : "Niet beschikbaar" }}
                </td>
                <td>{{ $parking->address }}</td>
                <td>
                        {{ $parking->parkingStatus->availableCapacity }} / {{ $parking->parkingStatus->totalCapacity }}
                </td>
            </tr>
        @endif
    @endforeach
@endsection



@section('beschikbaarheid')
    <div id="container" style="min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto"></div>
@endsection



@section('twitter')

    <hr/>
    <h3>Twitter robot</h3>
    <img src="https://pbs.twimg.com/profile_images/689562976177778691/n2cRcEoV.png" class="img-rounded img-responsive" alt="" width="75px" style="float:left; padding-right: 7px;"/>

    <a href="https://twitter.com/VrijeParkingG" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @VrijeParkingG</a><br/>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

    Aangezien stad Gent de realtime bezetting van zijn parkings ter beschikking stelt, kunnen wij zowel op onze website als op Twitter een continu reeël beeld
    geven van de beschikbare parking. Op twitter doen wij dit aan de hand van een robot die de data ophaalt per kwartier en vervolgens hier updates over geeft.
    Wanneer een parking minder dan 30% beschikbaarheid heeft wordt dit meegegeven. Ook wanneer de parking terug <b>meer</b> dan 30% heeft wordt dit meegedeeld.
    Dit alles met afwisseling van <b>"Summary tweets"</b> die een opsomming geven en tenslotte nog tweets die melden als een parking compleet volzet is.

@endsection



@section('centraleMapCoordinaten')
    51.0507644,3.7250077
@endsection


@section('parkingsOpKaartLijst')
    @foreach($data as $parking)
          ["{{ isset($parking->description) ? $parking->description : "Niet beschikbaar"   }}" , {{ $parking->latitude }}, {{ $parking->longitude  }}, "/parking/{{ isset($parking->description) ? strtolower($parking->description) : "Niet beschikbaar"  }}"],
      @endforeach
@endsection




@section('bezetting')
    @foreach($data as $parking)
        @if(isset($parking->parkingStatus->availableCapacity))
             {{ $parking->parkingStatus->totalCapacity - $parking->parkingStatus->availableCapacity }} +
        @endif
    @endforeach
@endsection

@section('totaal')
    @foreach($data as $parking)
        @if(isset($parking->parkingStatus->availableCapacity))
          {{ $parking->parkingStatus->totalCapacity }} +
        @endif
      @endforeach
@endsection