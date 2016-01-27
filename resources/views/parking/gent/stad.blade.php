@extends('../templates/stad_template')

@section('stadsNaam')
    {{ $stad }}
@endsection


@section('parkingLijst')
    @foreach($data as $parking)
        @if(isset($parking->parkingStatus->availableCapacity))
            <tr style="cursor:pointer" onclick="window.location.href='/parking/{{ isset($parking->description) ? $parking->description : "Niet beschikbaar" }}'" class="@if(($parking->parkingStatus->availableCapacity / $parking->parkingStatus->totalCapacity) < 0.10) danger
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


@section('centraleMapCoordinaten')
    51.0507644,3.7250077
@endsection


@section('parkingsOpKaartLijst')
    @foreach($data as $parking)
          ["{{ isset($parking->description) ? $parking->description : "Niet beschikbaar"   }}" , {{ $parking->latitude }}, {{ $parking->longitude  }}, "/parking/{{ isset($parking->description) ? $parking->description : "Niet beschikbaar"  }}"],
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