@extends('app')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">

        </div>
    </header>


    <style>
        .zoekParkings {
            background: #258cd1; /* For browsers that do not support gradients */
            background: -webkit-linear-gradient(#258cd1, white); /* For Safari 5.1 to 6.0 */
            background: -o-linear-gradient(#258cd1, white); /* For Opera 11.1 to 12.0 */
            background: -moz-linear-gradient(#258cd1, white); /* For Firefox 3.6 to 15 */
            background: linear-gradient(#258cd1, white); /* Standard syntax */
        }
    </style>

    <section style="color:white; background-color: #258cd1;" class="zoekParkings">
        <div class="row">
              <div class="col-md-5 col-md-offset-3">
                    <h2>Bedankt voor uw bijdrage!</h2>
                    <h4>Dankzij uw bijdrage is het mogelijk voor ons om gratis te blijven opereren.
                    Serverkosten & project-uren brengen immers de nodige kost met zich mee. Om dit te kunnen blijven doen zonder
                    het gebruik van storende advertenties zijn we afhankelijk van eigen middelen en derden.</h4>
              </div>

        </div>

    </section>


    <section style="text-shadow: 1px 1px black; color:white; height: 280px; background-color: black;">
        <div class="row" text-align="center" style="margin-top: -100px">
            <div class="col-lg-12 text-center">
                <h3>Mogelijk dankzij</h3>
            </div>
        </div>
        <div class="row" style="text-align: center;">
            <div class="col-lg-12 text-center">
                <img src="/img/parkingboys2.png" alt=""/>
                <img src="http://www.wetenschapsacademieblankenberge.be/images/logo_howest.png" alt=""/>
            </div>
        </div>
    </section>

@endsection