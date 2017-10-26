@extends('...app')

@section('content')

<header style="margin-top:-50px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2>Overzicht steden</h2>
            </div>
        </div>
    </div>
</header>

<style>
.meerdere-rijen {
    -webkit-column-count: 4; /* Chrome, Safari, Opera */
    -moz-column-count: 4; /* Firefox */
    column-count: 4;
}
</style>

<!-- Portfolio Grid Section -->
<section id="portfolio">
    <div class="container" style="margin-top: -50px">

        Hieronder vindt u een lijst van steden waar wij parkings van hebben. Ontbreekt een stad of zou je zelf graag data toevoegen? Contacteren kan via Contact@vrijeparking.be
        <br>
        Het is ook steeds mogelijk om in onze tool "Vind Parkeerplaats" zelf parkings toe te voegen. Na verificatie komt de stad waarin deze ligt in deze lijst te recht.

        <br><br>

        <div class="meerdere-rijen">
            @foreach($offline_steden as $stad)
                @if($stad->aantal_parkings() >= 2)
                    <a href="/stad/{{$stad->stad}}"><h4>{{ $stad->stad }}</h4></a>
                @endif
            @endforeach
        </div>
    </div>
</section>

@endsection