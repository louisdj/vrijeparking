@extends('../templates/parking_template')


@section('parkingTitle')
    {{ $parking->name_nl }} (Brussel)
@endsection

@section('parkingNaam')
    '{{ $parking->name_nl }} (Brussel)'
@endsection

@section('parkingFoto')
    <img src="/img/parkings/Brussel/{{$parking->name_nl}}.jpg" alt="" width="330px" height="220px" style="border-radius: 20px;"/>
@endsection


@section('omschrijving')
    @if($parkingDb->omschrijving != "")
        {{ $parkingDb->omschrijving }}
    @else
        Geen Omschrijving
    @endif

@endsection


@section('adres')
    {{ $parking->address_nl }}
@endsection

@section('contact')
    Geen contactinfo
@endsection



@section('beschikbaarheid')

    <b>Totaal: {{ $parking->total_places }} plaatsen</b><br/>
    <small>Geen realtime data  </small>

@endsection


@section('openingsUren')
    Maandag	07:00 - 01:00
    Dinsdag	07:00 - 01:00
    Woensdag	07:00 - 01:00
    Donderdag	07:00 - 01:00
    Vrijdag	07:00 - 02:00
    Zaterdag	07:00 - 02:00
    Zondag	10:00 - 01:00
    Feestdagen	10:00 - 01:00
@endsection


@section('parkingLocatie')
    "{{ $parking->latitude }}", "{{ $parking->longitude  }}"
@endsection