@extends('../templates/parking_template')


@section('parkingTitle')
    {{ $parking->name_nl }} (Brussel)
@endsection

@section('parkingNaam')
    '{{ $parking->name_nl }} (Brussel)'
@endsection

@section('parkingFoto')
    <img src="/img/parkings/Brussel/{{ str_replace("'", "", str_replace(["è", 'é'], "e", $parking->name_nl)) }}.jpg" alt="" width="330px" height="220px" style="border-radius: 20px;"/>
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

    {{--<table>--}}
        {{--<tr>--}}
            {{--<td>Maandag</td>--}}
            {{--<td>07:00 - 01:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Dinsdag</td>--}}
            {{--<td>07:00 - 01:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Woensdag</td>--}}
            {{--<td>07:00 - 01:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Donderdag</td>--}}
            {{--<td>07:00 - 01:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Vrijdag</td>--}}
            {{--<td>07:00 - 02:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Zaterdag</td>--}}
            {{--<td>07:00 - 02:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Zondag</td>--}}
            {{--<td>10:00 - 01:00</td>--}}
        {{--</tr>--}}
        {{--<tr>--}}
            {{--<td>Feestdagen</td>--}}
            {{--<td>10:00 - 01:00</td>--}}
        {{--</tr>--}}
    {{--</table>--}}

@endsection


@section('parkingLocatie')
    "{{ $parking->latitude }}", "{{ $parking->longitude  }}"
@endsection