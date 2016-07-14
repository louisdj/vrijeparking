@extends('app')

@section('content')

    <script src="/js/tabcontent.js" type="text/javascript"></script>
    <link href="/css/tabcontent.css" rel="stylesheet" type="text/css" />

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>klantenpaneel</h2>
                </div>
            </div>
        </div>
    </header>

    <section id="portfolio">
        <div class="container" style="margin-top: -150px">

            <div class="row">

                <div style="width: 100%; margin: 0 auto; padding: 100px 0 40px;">

                    <ul class="tabs" data-persist="true">
                            <li><a href="#view1">Overzicht parkings</a></li>
                            <li><a href="#view2">Prestatiegegevens</a></li>
                    </ul>
                    <div class="tabcontents">
                        <div id="view1">
                            <h3><b>Parkings {{ $stad->stad }}</b></h3>
                            <p style="border: 1px solid black;">
                                <b>Parkings in systeem: </b> {{ count($parkings) }}<br/>
                                <b>Data-source: </b> {{ $stad->url }}<br/>
                                <b>Coordinaten: </b> {{ $stad->coordinaten }}

                                <table class="table table-striped table-bordered table-hover text-center" >
                                    <tr class="info"  >
                                        <th class="text-center">Foto</th>
                                        <th class="text-center">Naam</th>
                                        <th class="text-center">Adres</th>
                                        <th class="text-center">Analyse</th>
                                    </tr>
                                    @foreach($parkings as $parking)

                                            <tr>
                                                <td style="vertical-align:middle"><img src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e", $parking->naam)) }}.jpg" alt="" width="150px" height="100px"/></td>
                                                <td style="vertical-align:middle">{{ $parking->naam }}</td>
                                                <td style="vertical-align:middle">{{ $parking->adres }}</td>
                                                <td style="vertical-align:middle"><a href="/beheer/klantenpaneel/parking/{{ $parking->id }}"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></td>
                                            </tr>

                                    @endforeach

                                </table>
                            </p>
                        </div>
                        <div id="view2">
                            <h3><b>Algemene analyse</b></h3>
                            <p>

                                Blabalba data hier.

                            </p>
                        </div>
                    </div>
                </div>

                {{--<h2>Blog</h2>--}}

            </div>

        </div>
    </section>

@endsection