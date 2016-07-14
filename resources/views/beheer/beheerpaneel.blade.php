@extends('app')

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
        <div class="container" style="margin-top: -150px">

            <div class="row">

                <div style="width: 100%; margin: 0 auto; padding: 120px 0 40px;">

                    <h2>Parkings</h2>

                    <ul class="tabs" data-persist="true">
                        @foreach($steden as $key => $stad)
                            <li><a href="#view{{ $key }}">{{ $stad->stad }}</a></li>
                        @endforeach
                    </ul>
                    <div class="tabcontents">
                        @foreach($steden as $key => $stad)
                            <div id="view{{ $key }}">
                                <h3><b>{{ $stad->stad }}</b></h3>
                                <p>

                                    <table class="table table-striped table-bordered table-hover text-center" >
                                        <tr class="info"  >
                                            <th class="text-center">Foto</th>
                                            <th class="text-center">Naam</th>
                                            <th class="text-center">Adres</th>
                                            <th class="text-center">Bewerken</th>
                                        </tr>
                                        @foreach($parkings as $parking)
                                            @if($parking->stad == strtolower($stad->stad))
                                                {{--<tr><td>{{ $parking->naam  }}</td></tr>--}}


                                                <tr>
                                                    <td style="vertical-align:middle"><img src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e", $parking->naam)) }}.jpg" alt="" width="150px" height="100px"/></td>
                                                    <td style="vertical-align:middle">{{ $parking->naam }}</td>
                                                    <td style="vertical-align:middle">{{ $parking->adres }}</td>
                                                    <td style="vertical-align:middle"><a href="/beheer/parking/{{ $parking->id }}"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></td>
                                                </tr>


                                            @endif
                                        @endforeach

                                    </table>
                                </p>

                                 {{--<a href=""><button class="btn-primary"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>Nieuwe parking toevoegen</button></a>--}}
                            </div>
                        @endforeach
                    </div>
                </div>

                <h2>Blog</h2>
                <table class="table table-striped table-bordered table-hover text-center" >
                    <tr class="info"  >
                        <th class="text-center">Datum</th>
                        <th class="text-center">Titel</th>
                        <th class="text-center">Inhoud</th>
                        <th class="text-center">Bewerken</th>
                    </tr>
                    @foreach($blogs as $blog)

                            <tr>
                                <td style="vertical-align:middle">{{ $blog->updated_at }}</td>
                                <td style="vertical-align:middle">{{ $blog->titel }}</td>
                                <td style="vertical-align:middle">{{ substr($blog->inhoud,0 , 300) }}...</td>
                                <td style="vertical-align:middle"><a href="/beheer/blog/{{ $blog->id }}"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></td>
                            </tr>

                    @endforeach
                </table>
                <a href="/beheer/blog/new"><button class="btn-primary"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>Nieuwe blog toevoegen</button></a>

            </div>

        </div>
    </section>

@endsection