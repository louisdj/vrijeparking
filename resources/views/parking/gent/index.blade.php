@extends('../templates/parking_template')


@section('parkingTitle')
    {{ $parking->description }} ({{ $parking->city->name  }})
@endsection

@section('parkingNaam')
    '{{ $parking->description }} ({{ $parking->city->name  }})'
@endsection


@section('parkingFoto')
    <img src="/img/parkings/{{ $parking->city->name }}/{{$parking->description}}.jpg" alt=""/>
@endsection


@section('beschikbaarheid')
    <div class="progress" style="height:20px; vertical-align: bottom; background-color: red;">
      <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"
      style="width: {{ 100 - round(($parking->parkingStatus->availableCapacity  /  $parking->parkingStatus->totalCapacity) * 100) }}%; font-size:20px; padding-top: 4px;">
        {{ 100 - round(($parking->parkingStatus->availableCapacity  /  $parking->parkingStatus->totalCapacity) * 100) }}%
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


@section('parkingLocatie')
    "{{ $parking->latitude }}", "{{ $parking->longitude  }}"
@endsection