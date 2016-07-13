@extends('...app')

@section('content')

        <script src="/js/tabcontent.js" type="text/javascript"></script>
        <link href="/css/tabcontent.css" rel="stylesheet" type="text/css" />

        <header>
            <div class="container" style="padding-top:110px">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h2>beheerpaneel</h2>
                    </div>
                </div>
            </div>
        </header>

        <section id="portfolio">
            <div class="container" style="margin-top: -100px">
                <ul class="pager">
                    <li class="previous"><a href="/beheer">Terug naar overzicht</a></li>
                </ul>

                <div class="row">
                    <div style="width: 97%; margin: 0 auto; padding: 20px 0 40px;">
                        <ul class="tabs" data-persist="true">
                            <li><a href="#view">{{ $parking->naam }}</a></li>
                        </ul>
                        <div class="tabcontents">
                            <div id="view">
                                <form action="/beheer/parking/{{ $parking->id }}/update" method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <h2><b>{{ $parking->naam }}</b></h2>
                                    <p>
                                        <div id="container" style="height: 400px; min-width: 310px"></div>



                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

@endsection