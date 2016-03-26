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