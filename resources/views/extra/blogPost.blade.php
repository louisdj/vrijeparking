@extends('app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Blog - {{ $blog->titel }}</h2>
                </div>
            </div>
        </div>
    </header>

    <section id="portfolio">
        <div class="container">


            <div class="row">

                    <img src="{{ $blog->afbeelding }}" class="img-rounded" width="300px" style="float:left; padding: 15px; padding-top: 0px;">
                        <b>{{ $blog->updated_at }} <i> - {{ $blog->auteur }}</i></b><br/><br/>

                        {!! $blog->inhoud !!}
                        <br/><br/>



             </div>



    </section>


@endsection