@extends('app')


@section('content')

    <header>
            <div class="container" style="padding-top:110px">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h2>Testpagina om embed te bekijken</h2>
                    </div>
                </div>
            </div>
        </header>

        <section id="portfolio">
            <div class="container">

                Beste Gentenaars, onderstaand vindt u al onze parkinginfo.


                    <script type='text/javascript' charset='utf-8'>
                       var iframe = document.createElement('iframe');
                       document.body.appendChild(iframe);

                       iframe.src = 'http://localhost:8000/embed/';
                       iframe.frameBorder = 1;
                       iframe.width = '100%';
                       iframe.height = '800px';
                    </script>


            </div>
        </section>

@endsection
