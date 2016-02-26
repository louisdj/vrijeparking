@extends('../templates/parking_template')


@section('parkingTitle')
    {{ $parking->description }} ({{ $parking->city->name  }})
@endsection

@section('parkingNaam')
    '{{ $parking->description }} ({{ $parking->city->name  }})'
@endsection


@section('parkingFoto')
    <img src="/img/parkings/gent/{{$parking->description}}.jpg" alt=""/>
@endsection

@section('omschrijving')

    {{ $parkingDb->omschrijving }}

@endsection


@section('adres')
    {{ $parking->address }}
@endsection

@section('contact')
    {{ $parking->contactInfo }}
@endsection


@section('beschikbaarheid')
    <div class="progress" style="height:20px; vertical-align: bottom; background-color: red;">
      <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"
      style="width: {{ round(($parking->parkingStatus->availableCapacity  /  $parking->parkingStatus->totalCapacity) * 100) }}%; font-size:20px; padding-top: 4px;">
        {{ round(($parking->parkingStatus->availableCapacity  /  $parking->parkingStatus->totalCapacity) * 100) }}%
      </div>
    </div>
@endsection


@section('openingsUren')
    {{--{{ dd($parking) }}--}}
    @foreach($parking->openingTimes[0]->days as $day)
        {{ $day }} <br/>
    @endforeach
    <hr/>
    <b>{{ $parking->openingTimes[0]->from }} - {{ $parking->openingTimes[0]->to }} </b>
@endsection



@section('tarieven')
    <hr/>
    <h4>Tarieven</h4>
    <h6>Dagtarief (van maandag tot en met zaterdag, van 7 tot 19 uur)</h6>
    <table class="table table-bordered">
        <tr class="info">
            <th>15 m</th>
            <th>30 m</th>
            <th>45 m</th>
            <th>1 u</th>
            <th>1 u 20 m</th>
            <th>1 u 40 m</th>
            <th>2 u</th>
            <th>2 u 20 m</th>
            <th>2 u 40 m</th>
            <th>3 u</th>
            <th>3 u 20 m</th>
            <th>3 u 40 m</th>
            <th>4 u</th>
            <th>4 u 20 m</th>
            <th>4 u 40 m</th>
            <th>5 u</th>
            <th>6 u</th>
            <th>7 u</th>
            <th>8u +</th>
        </tr>
        <tr>
            <td>€ 0,50</td>
            <td>€ 1</td>
            <td>€ 1,50</td>
            <td>€ 2</td>
            <td>€ 2,50</td>
            <td>€ 3</td>
            <td>€ 3,50</td>
            <td>€ 4</td>
            <td>€ 4,50</td>
            <td>€ 5</td>
            <td>€ 5,50</td>
            <td>€ 6</td>
            <td>€ 6,50</td>
            <td>€ 7</td>
            <td>€ 7,50</td>
            <td>€ 8</td>
            <td>€ 9</td>
            <td>€ 10</td>
            <td>€ 11</td>
        </tr>
    </table>

    <h6>Avond- en nachttarief (elke dag van de week, van 19 tot 7 uur)</h6>
    <h6>Zondagtarief (op zon- en feestdagen, van 7 tot 19 uur)</h6>
    <table class="table table-bordered">
        <tr class="info">
            <th>1 uur</th>
            <th>2 uur</th>
            <th>3 uur</th>
            <th>4 uur en volgende</th>
        </tr>

        <tr>
            <td>€ 0,80</td>
            <td>€ 1,70</td>
            <td>€ 2,50</td>
            <td>€ 3,50</td>
        </tr>
    </table>
@endsection



@section('parkingLocatie')
    "{{ $parking->latitude }}", "{{ $parking->longitude  }}"
@endsection