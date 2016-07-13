@extends('...app')

@section('head')

    <script type="text/javascript">var switchTo5x=true;</script>
    <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
    <script type="text/javascript">stLight.options({publisher: "8dd35d2c-9cfe-4297-931a-d0009f436d66", doNotHash: false, doNotCopy: false, hashAddressBar: true});</script>

@endsection


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

             <span class='st_facebook_large' displayText='Facebook'></span>
             <span class='st_twitter_large' displayText='Tweet'></span>
             <span class='st_linkedin_large' displayText='LinkedIn'></span>
             <span class='st_email_large' displayText='Email'></span>



    </section>


@endsection