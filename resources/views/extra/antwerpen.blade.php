@extends('app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Parkings stad antwerpen</h2>
                </div>
            </div>
        </div>
    </header>

    <section id="portfolio">
        <div class="container">

            <?php echo date('r') ?>

            <p>
                Aangezien wij veel problemen ondervonden met gebruikers uit Antwerpen die niet
                goed begrepen waar ze precies konden parkeren, hebben we speciaal deze extra sectie
                aan de website toegevoegd. Het bied een duidelijk overzicht aan de gemiddelde
                Antwerpenaar en zijn nood aan parkeerplaats.
            </p>

            <hr/>

            <div class="col-lg-12 text-center">
                <img class="img-responsive" src="\img\parkings\antwerpen\antwerpen stad.png" alt="" style="margin: 0 auto"/>

                <br/><br/>

                <iframe width="90%" height="615" src="https://www.youtube.com/embed/j3K0uB6DO0E?rel=0&autoplay=1" frameborder="0" allowfullscreen></iframe>
                <img src="\img\parkings\antwerpen\1eravril.jpg" alt=""/>
            </div>


        </div>
    </section>


@endsection