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