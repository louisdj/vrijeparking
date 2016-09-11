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
                            <li><a href="#view">Nieuwe parking</a></li>
                        </ul>
                        <div class="tabcontents">
                            <div id="view">
                                <form action="/beheer/parking/new" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <h2><b>Nieuwe parking</b></h2>
                                <p>
                                    <table class="table table-striped table-bordered table-hover text-center" >
                                        <tr class="info"  >
                                            <th class="text-center">Naam</th>
                                            <th class="text-center">Waarde</th>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Naam</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value="" name="naam"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Stad</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value=""  name="stad"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Adres</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value=""  name="adres"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Afbeelding</td>
                                            <td style="vertical-align:middle"><input type="file" class="form-control" value="" name="afbeelding"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Lat/Long</td>
                                            <td style="vertical-align:middle">
                                            <input type="text" class="form-control" value=""  name="latitude"/>,
                                            <input type="text" class="form-control" value=""  name="longitude">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="vertical-align:middle">Omschrijving</td>
                                            <td style="vertical-align:middle"><input type="textarea" rows="5" class="form-control" value="" name="omschrijving"/></td>
                                        </tr>

                                        <tr>
                                            <td style="vertical-align:middle">Telefoon</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value="" name="telefoon"/></td>
                                        </tr>

                                        <tr>
                                            <td style="vertical-align:middle">Totaal # plaatsen</td>
                                            <td style="vertical-align:middle"><input type="text" class="form-control" value="" name="totaal_plaatsen"/></td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <hr/>

                                        <tr>
                                            <td style="vertical-align:middle">Bericht</td>
                                            <td style="vertical-align:middle"><input type="textarea" rows="2" class="form-control" value=""  name="bericht"/></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:middle">Type bericht</td>
                                            <td style="vertical-align:middle">
                                                <select name="type">
                                                      <option value="0" selected>Info</option>
                                                      <option value="1">Positief</option>
                                                      <option value="2">Opgepast</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Live data:</td>
                                            <td>
                                                <select name="live_data">
                                                      <option value="0" selected>Niet live</option>
                                                      <option value="1">Live</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </p>

                                <h3>Betaalmiddelen</h3>
                                <select name="betaalmiddelen[]" multiple>
                                    <option value="1">Maestro</option>
                                    <option value="2">Bancontact</option>
                                    <option value="3">Visa</option>
                                    <option value="4">Mastercard</option>
                                    <option value="5">cash</option>
                                </select>

                                {{--<input type="checkbox" name="maestro" value="1"><label for="maestro">Maestro</label><br>--}}
                                {{--<input type="checkbox" name="bancontact" value="2"><label for="bancontact">Bancontact</label><br>--}}
                                {{--<input type="checkbox" name="visa" value="3"><label for="visa">visa</label><br>--}}
                                {{--<input type="checkbox" name="mastercard" value="4"><label for="mastercard">mastercard</label><br>--}}
                                {{--<input type="checkbox" name="cash" value="5"><label for="cash">cash</label>--}}

                                <br/><br/>

                                 <a href=""><button class="btn-primary"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Creëer parking</button></a>
                                 <a href=""><button class="btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Annuleren</button></a>
                                 </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

@endsection