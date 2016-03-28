@extends('app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Team</h2>
                </div>
            </div>
        </div>
    </header>

    <section id="portfolio">
        <div class="container">

            <p>
                De data waar VrijeParking van gebruik maakt wordt beschikbaar gestelt door een grote groep mensen bij zowel steden als bedrijven.
                De mensen achter VrijeParking zelf, die instaan voor de verwerking en presentatie van deze data in de web-browser en mobiele app zijn hieronder vermeld.
                Het team bestaat uit gemotiveerde laatste jaarsstudenten toegepaste informatica met een grote interesse in open data.
            </p>

            <hr/>

            <div class="row">
                <div class="col-xs-3 col-md-offset-3" align="center">
                    <div class="row">
                        <img src="/img/team/robbert.jpg" width="200px" class="img-circle">
                    </div>
                    <div class="row"><br/>
                        <b>Robbert Goeminne</b> <br/>
                        Website/API/Twitter Robot
                    </div>
                </div>

                <div class="col-xs-3" align="center">
                    <div class="row">
                        <img src="/img/team/louis.jpg" width="200px" class="img-circle">
                    </div>
                    <div class="row"><br/>
                        <b>Louis De Jaeger</b> <br/>
                        Mobile App
                    </div>
                </div>
            </div>



        </div>
    </section>

@endsection