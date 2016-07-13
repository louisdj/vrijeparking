@extends('...app')

@section('content')

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

                    <div class="tabcontents">
                        <div id="view">
                            <form action="/beheer/blog/{{ $blog->id }}/update" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <h2><b></b></h2>
                            <p>
                                <table class="table table-striped table-bordered table-hover text-center" >
                                    <tr class="info"  >
                                        <th class="text-center">Veld</th>
                                        <th class="text-center">Waarde</th>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:middle">Titel</td>
                                        <td style="vertical-align:middle"><input type="text" class="form-control" value="{{ $blog->titel }}" name="titel"/></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:middle">Inhoud</td>
                                        <td style="vertical-align:middle"><textarea class="form-control" rows="5" id="inhoud" name="inhoud">{{ $blog->inhoud }}</textarea></td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:middle">Afbeelding</td>
                                        <td style="vertical-align:middle">
                                        <input type="text" class="form-control" value="{{ $blog->afbeelding }}"  name="afbeelding"/>,
                                        </td>
                                    </tr>
                                </table>
                            </p>

                             <a href="/beheer/blog/{{$blog->id}}/update"><button class="btn-primary"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Update blogpost</button></a>
                             <a href="/beheer/blog/{{$blog->id}}/remove"><button type="button" class="btn-danger"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Remove blogpost</button></a>
                             </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection