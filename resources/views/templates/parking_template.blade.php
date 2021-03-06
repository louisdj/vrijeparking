@extends('...app')

@section('content')

    <header style="margin-top:-50px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Parking {{ $parking->naam }} ({{ $parking->stad  }})</h2>
                </div>
            </div>
        </div>
    </header>

    <!-- Portfolio Grid Section -->
    <section id="portfolio">
        <div class="container" style="margin-top: -50px">

            @if($parking->bericht)
                <div class="alert @if($parking->bericht_type == 0)alert-info
                                  @elseif($parking->bericht_type == 1)alert-success
                                  @else alert-warning
                                  @endif alert-dismissible fade in" role="alert">

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button> <strong>Opgepast!</strong>

                    {{ $parking->bericht }}
                </div>
            @endif

            <div class="row">
                {{--<h3>Details</h3>--}}
                <div class="col-md-4">
                    {{--<img src="/img/parkings/{{ $parking->stad }}/{{strtolower($parking->naam)}}.jpg" alt="" width="330px" height="220px" style="border-radius: 20px;"/>--}}
                    <img class="img-responsive" onerror="this.src='https://maps.googleapis.com/maps/api/streetview?size=400x300&location={{ $parking->latitude }},{{ $parking->longitude }}&fov=120&heading=130&pitch=10&key=AIzaSyConkrSh5Gm0OcY_l5_pRCCrKPhR0qTgJw'" src="/img/parkings/{{$parking->stad}}/{{ strtolower(str_replace(["é","è"], "e", $parking->naam)) }}.jpg" alt="" width="330px" height="220px" style="border-radius: 20px;"/>

                    <br/><br/>

                    @foreach($parking_betaalmogelijkheden as $betaalmogelijkheid)
                        <img src="/img/betaalmogelijkheden/{{ strtolower($betaalmogelijkheid->betaalmiddel->middel) }}.png"
                        width="45px" alt="" style="padding-right: 5px;" data-placement="bottom" data-toggle="tooltip" title="{{ $betaalmogelijkheid->betaalmiddel->middel }}" />
                    @endforeach

                    <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip();
                        });
                    </script>

                    <br/>

                    @if($parking->bewaakt)
                        <img src="/img/parkingvoorzieningen/bewaker.png" width="23px" data-toggle="tooltip" title="Bewaakt" />
                    @endif

                    @if($parking->gehandicapten_plaatsen)
                        <img src="/img/parkingvoorzieningen/gehandicapt.png" width="23px" alt="" data-toggle="tooltip" title="Plaatsen gehandicapten" /> {{ $parking->gehandicapten_plaatsen }}
                    @endif

                    @if($parking->maximale_hoogte)
                        <br/> <b>Hoogte:</b> {{ $parking->maximale_hoogte }}m
                    @endif



                </div>
                <div class="col-md-6">

                    <h4>Omschrijving</h4>
                        @if($parking->omschrijving)
                            {{ $parking->omschrijving }}
                        @else
                            /
                        @endif
                    <br/><br/>

                    <h4>Adres</h4>
                        {{ $parking->adres }}
                    <br/><br/>

                    <h4>Contact</h4>
                        @if($parking->telefoon)
                            <i class="fa fa-phone" aria-hidden="true"></i> {!! $parking->telefoon !!}
                        @endif
                        @if($parking->email && $parking->telefoon)
                            <br/><i class="fa fa-envelope" aria-hidden="true"></i> <a href="mailto:{!! $parking->email !!}">{!! $parking->email !!}</a>
                        @elseif($parking->email)
                            <i class="fa fa-envelope" aria-hidden="true"></i> <a href="mailto:{!! $parking->email !!}">{!! $parking->email !!}</a>
                        @endif
                        @if(!$parking->telefoon && !$parking->email)
                            /
                        @endif
                    <br/><br/>

                </div>
                {{--<div class="col-md-2">--}}
                    {{----}}
                {{--</div>--}}
                <div class="col-md-2" style="border: 1px solid black; padding:10px; text-align:center">
                    <h4>Beschikbaar</h4>

                    <style>
                        .progress {
                          position: relative;
                        }

                        .progress span {
                            position: absolute;
                            display: block;
                            width: 100%;
                        }
                    </style>

                    @if($parking->totaal_plaatsen == 0)
                        Geen data
                    @elseif($parking->live_data == 0)
                       - / {{ $parking->totaal_plaatsen }}
                    @else
                        <div class="progress" style="height:20px; vertical-align: bottom; background-color: red;">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"
                            style="width: {{ round(($parking->beschikbare_plaatsen  /  $parking->totaal_plaatsen) * 100) }}%; font-size:20px; padding-top: 4px;">
                                <span class="show">{{ round(($parking->beschikbare_plaatsen  /  $parking->totaal_plaatsen) * 100) }}% ({{$parking->beschikbare_plaatsen}}/{{$parking->totaal_plaatsen}})</span>
                            </div>
                        </div>
                    @endif

                    <h4>Openingsuren</h4>

                    <?php $dowMap = array('Ma', 'Di', 'Woe', 'Do', 'Vrij', 'Zat', 'Zon'); ?>

                    <table align="center" style="border-collapse:separate; border-spacing: 5px;">

                        @if(count($openingsuren) > 0)
                            @foreach($openingsuren as $dag)

                                <tr @if(date('w') == $dag->dag || date('w') + 7 == $dag->dag)style="font-weight: bold; color: royalblue; font-size: 17px;" @endif>
                                    <td><b>{{ $dowMap[$dag->dag -1]  }}</b></td>
                                    <td>{{ date('H:i', strtotime($dag->openingsuur)) }} - {{ date('H:i', strtotime($dag->sluitingsuur)) }}</td>
                                </tr>
                            @endforeach
                        @else
                            Geen data
                        @endif

                    </table>
                </div>
            </div>

            @if(count($tarievenDag) > 0)

            <hr/>

            <h4>Tarieven</h4>
            <h6>{!! $parking->dagtarief !!}</h6>
            <div class="table-responsive">
            <table class="table table-bordered">
                <tr class="info">
                    @foreach($tarievenDag as $tarief)

                        {{--{{ date('H:i', strtotime($tarief->tijdsduur)) }}--}}

                        {{--<th>@if(str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) >= 15)--}}
                             {{--{{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}m--}}
                            {{--@else--}}
                            {{--{{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}u--}}
                            {{--@endif </th>--}}

                        <th>
                            @if(strpos(date('H:i', strtotime($tarief->tijdsduur)), '00:') !== false)
                                {{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}m
                            @else
                                {{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}u
                            @endif
                        </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($tarievenDag as $tarief)
                        <td>@if($tarief->prijs == 0) Gratis @else €{{ number_format($tarief->prijs, 2) }} @endif</td>
                    @endforeach
                </tr>
            </table>
            </div>

            @if($parking->stad != "gent")
            <h6>{!! $parking->nachttarief !!}</h6>
            <div class="table-responsive">
            <table class="table table-bordered">
                <tr class="info">
                    @foreach($tarievenNacht as $tarief)
                        <th>
                            @if(strpos(date('H:i', strtotime($tarief->tijdsduur)), '00:') !== false)
                                {{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}m
                            @else
                                {{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}u
                            @endif
                        </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($tarievenNacht as $tarief)
                        <td>@if($tarief->prijs == 0) Gratis @else €{{ number_format($tarief->prijs, 2) }} @endif</td>
                    @endforeach
                </tr>
            </table>
            </div>
            @else
            <br>
            <h6>Tijdens de winterfeesten gelden de dagtarieven gedurende de hele dag.</h6>
            @endif

            @endif

            @if($historie != null)

            <hr/>
            <h4>Voorspelling</h4>
            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

            <script>
                $(function () {
                    $('#container').highcharts({
                        chart: {
                            type: 'spline'
                        },
                        title: {
                            text: 'Bezetting parking'
                        },
                        subtitle: {
                            text: '<?php echo(date('l')) ?>'
                        },
                        xAxis: {
                            type: 'datetime',
                            labels: {
                                overflow: 'justify'
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Bezetting parking'
                            },
                            minorGridLineWidth: 0,
                            gridLineWidth: 0,
                            alternateGridColor: null,
                            plotBands: [{ // Light air
                                from: 0.0,
                                to: 100.0,
                                color: 'rgba(68, 170, 213, 0.1)',
                                label: {
                                    text: 'Leeg',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }, { // Light breeze
                                from: 100,
                                to: 200,
                                color: 'rgba(0, 0, 0, 0)',
                                label: {
                                    text: 'Lichte bezetting',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }, { // Gentle breeze
                                from: 200,
                                to: 300,
                                color: 'rgba(68, 170, 213, 0.1)',
                                label: {
                                    text: 'Gemiddeld',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }, { // Moderate breeze
                                from: 300,
                                to: 400,
                                color: 'rgba(0, 0, 0, 0)',
                                label: {
                                    text: 'Druk',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }, { // Fresh breeze
                                from: 500,
                                to: 600,
                                color: 'rgba(68, 170, 213, 0.1)',
                                label: {
                                    text: 'Bijna vol',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }, { // Strong breeze
                                from: 600,
                                to: 650,
                                color: 'rgba(0, 0, 0, 0)',
                                label: {
                                    text: 'Volzet',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }]
                        },
                        tooltip: {
                            valueSuffix: ' bezet'
                        },
                        plotOptions: {
                            spline: {
                                lineWidth: 4,
                                states: {
                                    hover: {
                                        lineWidth: 5
                                    }
                                },
                                marker: {
                                    enabled: false
                                },
                                pointInterval: 300000, // one hour
                                pointStart: Date.UTC({{ date('Y,m,d', strtotime(' -1 month')) }}, 0, 0, 0)
                            }
                        },
                        series: [
                        {
                            name: 'Vorige week',
                            data: [

                            @foreach($historie as $tijdstip)
                                {{ $tijdstip->bezetting }},
                            @endforeach

                            ]

                        }, {
                            name: 'Gemiddeld',
                            data: [

                            @foreach($historieAverage as $key => $tijdstip)
                                {{ $tijdstip }},
                            @endforeach

                            ]
                        },
                        {
                            name: 'Trend vandaag',
                            data: [

                            @foreach($bezettingVandaag as $tijdstip)
                                {{ $tijdstip->bezetting }},
                            @endforeach

                            ]

                        }


                        ],
                        navigation: {
                            menuItemStyle: {
                                fontSize: '10px'
                            }
                        }
                    });
                });
            </script>

            @endif

            <hr/>

            <div class="row">
                <h3>Kaart <small> Klik op de kaart om in te zoomen</small></h3>

                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
                <script type="text/javascript" src="http://gc.kis.scr.kaspersky-labs.com/1B74BD89-2A22-4B93-B451-1C9E1052A0EC/main.js" charset="UTF-8"></script><script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>


                <div class="col-md-12">
                    <div id='map'></div>
                    <script>
                        var mymap = L.map('map').setView([{{ $parking->latitude }},{{ $parking->longitude  }}],14);

                        mymap.scrollWheelZoom.disable();
                        mymap.on('click', function() {
                          if (mymap.scrollWheelZoom.enabled()) {
                                mymap.scrollWheelZoom.disable();
                            }
                            else {
                                mymap.scrollWheelZoom.enable();
                            }
                          });

                        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
                            maxZoom: 18,
                            id: 'mapbox.streets'
                        }).addTo(mymap);

                        var parking = L.icon({
                            iconUrl: '/img/parkings/P.png',

                            iconSize:     [35, 55], // size of the icon
                            iconAnchor:   [20, 55], // point of the icon which will correspond to marker's location
                            popupAnchor:  [-3, -56] // point from which the popup should open relative to the iconAnchor
                        });

                        var parkandride = L.icon({
                            iconUrl: '/img/parkings/PR.png',

                            iconSize:     [35, 55], // size of the icon
                            iconAnchor:   [20, 55], // point of the icon which will correspond to marker's location
                            popupAnchor:  [-3, -56] // point from which the popup should open relative to the iconAnchor
                        });

                        //Trimmed
                        L.marker([{{ $parking->latitude  }}, {{ $parking->longitude }}]@if($parking->parkandride),{icon: parkandride}@else,{icon: parking}@endif).addTo(mymap).bindPopup("{{trim($parking->adres)}}").openPopup();

                    </script>

                </div>
            </div>
        </div>
    </section>

@endsection