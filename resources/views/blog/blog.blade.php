@extends('...app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Blog</h2>
                </div>
            </div>
        </div>
    </header>

    <section id="portfolio">
        <div class="container">

            <p>
                Met VrijeParking willen we streven naar complete transparantie van onze acties en doelen. Met deze blog hopen we
                om steeds gebruikers en (potentiële) partners steeds op de hoogte te houden van onze ontwikkeling.
                We streven er ook naar om steeds bereikbaar te zijn voor feedback of opmerkingen. Dit kan altijd
                via contact@vrijeparking.be of één van onze social media kanalen.
            </p>

            <hr/>


            @foreach($posts as $post)

            <div class="row">
                <div class="col-sm-3">
                    <img src="{{ $post->afbeelding }}" class="img-rounded" width="200px">
                </div>
                <div class="col-sm-9">
                    <div class="row">
                        <a href="/blog/{{ $post->titel }}"><h3>{{ $post->titel }}</h3></a>
                    </div>
                    <div class="row">
                        {{ substr(strip_tags($post->inhoud), 0, 300) }}... <a href="/blog/{{ $post->titel }}"> Lees meer</a>
                        <br/>
                        <b><i>door {{ $post->auteur }}</i></b><br/>
                    </div>
                </div>
             </div>

             <hr/>

            @endforeach

    </section>


@endsection