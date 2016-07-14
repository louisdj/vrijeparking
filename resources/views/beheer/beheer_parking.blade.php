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
                                    <table class="table table-striped table-bordered table-hover text-center" >
                                        <tr class="info"  >
                                            <th class="text-center">Naam</th>
                                            <th class="text-center">Waarde</th>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Naam</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value="{{ $parking->naam }}" name="naam"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Stad</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value="{{ $parking->stad }}"  name="stad"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Lat/Long</td>
                                            <td style="vertical-align:middle">
                                            <input type="text" class="form-control" value="{{ $parking->latitude }}"  name="latitude"/>,
                                            <input type="text" class="form-control" value="{{ $parking->longitude }}"  name="longitude">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Bericht</td>
                                            <td style="vertical-align:middle"><input type="textarea" rows="2" class="form-control" value="{{ $parking->bericht }}"  name="bericht"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Type bericht</td>
                                            <td style="vertical-align:middle">
                                                <select name="type">
                                                      <option value="0" @if($parking->bericht_type==0) selected @endif>Info</option>
                                                      <option value="1" @if($parking->bericht_type==1) selected @endif>Positief</option>
                                                      <option value="2" @if($parking->bericht_type==2) selected @endif>Opgepast</option>
                                                </select>

                                            </td>
                                        </tr>
                                    </table>
                                </p>

                                 <a href=""><button class="btn-primary"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Update parking</button></a>
                                 <a href=""><button class="btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Deactiveer parking</button></a>
                                 </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

@endsection