@extends('../templates/stad_template')

@section('stadsNaam')
    {{ $stad }}
@endsection


@section('parkingLijst')
    @foreach($parkings as $parking)
        @if(isset($parking->beschikbare_plaatsen))
            <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->naam) ? strtolower(addslashes($parking->naam)) : "Niet beschikbaar" }}'" class="@if(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.10) danger
                        @elseif(($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) < 0.30) warning @endif">
                <td>
                    <img height="25px" src="http://www.downtownseattle.com/assets/2013/07/parking-icon.gif" alt=""/>
                    {{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar" }}
                </td>
                <td>{{ $parking->adres }}</td>
                <td>
                        {{ $parking->beschikbare_plaatsen }} / {{ $parking->totaal_plaatsen }}
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

    <a href="https://twitter.com/VrijeParkingK" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @VrijeParkingG</a><br/>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

    Aangezien stad Kortrijk de realtime bezetting van zijn parkings ter beschikking stelt, kunnen wij zowel op onze website als op Twitter een continu reeël beeld
    geven van de beschikbare parking. Op twitter doen wij dit aan de hand van een robot die de data ophaalt per kwartier en vervolgens hier updates over geeft.
    Wanneer een parking minder dan 30% beschikbaarheid heeft wordt dit meegegeven. Ook wanneer de parking terug <b>meer</b> dan 30% heeft wordt dit meegedeeld.
    Dit alles met afwisseling van <b>"Summary tweets"</b> die een opsomming geven en tenslotte nog tweets die melden als een parking compleet volzet is.

@endsection



@section('centraleMapCoordinaten')
    50.8295731,3.2689686
@endsection


@section('parkingsOpKaartLijst')
    @foreach($parkings as $parking)
          ["{{ isset($parking->naam) ? $parking->naam : "Niet beschikbaar"   }}" , {{ $parking->latitude }}, {{ $parking->longitude  }}, "/parking/{{ isset($parking->naam) ? strtolower($parking->naam) : "Niet beschikbaar"  }}"],
    @endforeach
@endsection




@section('bezetting')
    @foreach($parkings as $parking)
        @if(isset($parking->beschikbare_plaatsen))
             {{ $parking->totaal_plaatsen - $parking->beschikbare_plaatsen }} +
        @endif
    @endforeach
@endsection

@section('totaal')
    @foreach($parkings as $parking)
        @if(isset($parking->beschikbare_plaatsen))
          {{ $parking->totaal_plaatsen }} +
        @endif
      @endforeach
@endsection

@section('scripts')
    <script src="/js/chart.js"></script>
@endsection