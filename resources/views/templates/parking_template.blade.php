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
        <div class="container">


            <div class="row">
                {{--<h3>Details</h3>--}}
                <div class="col-md-4">
                    <img src="/img/parkings/{{ $parking->stad }}/{{strtolower($parking->naam)}}.jpg" alt="" width="330px" height="220px" style="border-radius: 20px;"/>

                    <?php $mogelijkheden = array('Maestro', 'Bancontact', 'Visa', 'Mastercard', 'Cash', 'Iets', 'anders'); ?>
                    <br/><br/>

                    @foreach($parking_betaalmogelijkheden as $betaalmogelijkheid)
                        {{--{{ dd($betaalmogelijkheid->Betaalmiddel) }}--}}

                        <img src="/img/betaalmogelijkheden/{{ strtolower($mogelijkheden[$betaalmogelijkheid->betaling_id-1]) }}.png"
                        width="45px" alt="" style="padding-right: 5px;" />
                    @endforeach

                </div>
                <div class="col-md-6">

                    <h4>Omschrijving</h4>
                        {{ $parking->omschrijving }}
                    <br/><br/>

                    <h4>Adres</h4>
                        {{ $parking->adres }}
                    <br/><br/>

                    <h4>Contact</h4>
                        {!! $parking->telefoon !!}
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
                                <tr @if(date('w') == $dag->dag)style="font-weight: bold; color: royalblue; font-size: 17px;" @endif>
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

            <hr/>



            <h4>Tarieven</h4>
            @if(count($tarievenDag) == 0) Niet beschikbaar @endif
            <h6>{!! $parking->dagtarief !!}</h6>
            <div class="table-responsive">
            <table class="table table-bordered">
                <tr class="info">
                    @foreach($tarievenDag as $tarief)
                        <th>@if(str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) >= 15)
                             {{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}m
                            @else
                            {{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}u
                            @endif </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($tarievenDag as $tarief)
                        <td>@if($tarief->prijs == 0) Gratis @else €{{ number_format($tarief->prijs, 2) }} @endif</td>
                    @endforeach
                </tr>
            </table>
            </div>

            <h6>{!! $parking->nachttarief !!}</h6>
            <div class="table-responsive">
            <table class="table table-bordered">
                <tr class="info">
                    @foreach($tarievenNacht as $tarief)
                        <th>{{ str_replace(['00:',':00'], '', date('H:i', strtotime($tarief->tijdsduur))) }}u</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($tarievenNacht as $tarief)
                        <td>@if($tarief->prijs == 0) Gratis @else €{{ number_format($tarief->prijs, 2) }} @endif</td>
                    @endforeach
                </tr>
            </table>
            </div>

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
                                pointStart: Date.UTC({{ date('Y,m,d', strtotime(' -7 days')) }}, 0, 0, 0)
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

            <hr/>

            <div class="row">
                <h3>Kaart</h3>
                <div class="col-md-12">
                    <script>

                      function initialize()
                      {
                           var mapOptions = {
                             zoom: 15,
                             center: new google.maps.LatLng("{{ $parking->latitude }}", "{{ $parking->longitude  }}")
                           };

                           var map = new google.maps.Map(document.getElementById("map"), mapOptions);

                           var contentString = '<div id="content">'+
                                 '<div id="siteNotice">'+
                                 '</div>'+
                                 '<h3 id="firstHeading" class="firstHeading">'+
                                 '{{ $parking->naam }} ({{ $parking->stad  }})'+
                                 '</h3>'+
                                 '</div>';

                             var infowindow = new google.maps.InfoWindow({
                               content: contentString
                             });

                             var marker = new google.maps.Marker({
                               position: new google.maps.LatLng("{{ $parking->latitude }}", "{{ $parking->longitude  }}"),
                               map: map,
                               title: '{{ $parking->naam }} ({{ $parking->stad  }})'
                             });

                             infowindow.open(map, marker);

                             marker.addListener('click', function() {
                               infowindow.open(map, marker);
                             });
                      }
                      google.maps.event.addDomListener(window, 'load', initialize);
                    </script>

                    <div id="map"></div>
                </div>
            </div>
        </div>
    </section>

@endsection