@extends('app_community')

@section('content')

    <header>
        <div class="container" style="padding-top:110px">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Start</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <p>Bedankt om mee te bouwen aan een parking-vriendelijk land.<p>
                </div>
            </div>
        </div>
    </header>


    <section id="portfolio">
        <div class="container">

            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

            <script>
                $(function () {
                    Highcharts.chart('container', {
                        chart: {
                            type: 'spline'
                        },
                        title: {
                            text: 'Parkings toegevoegd door community'
                        },
                        subtitle: {
                            text: 'Sinds de lancering op 18 november 2016'
                        },
                        xAxis: {
                            type: 'datetime',
                            labels: {
                                overflow: 'justify'
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Aantal parkings'
                            },
                            minorGridLineWidth: 0,
                            gridLineWidth: 0,
                            alternateGridColor: null,
                            plotBands: [{ // Light air
                                from: 1,
                                to: 10,
                                color: 'rgba(68, 170, 213, 0.1)',
                                label: {
                                    text: 'Erin komen',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }, { // Light breeze
                                from: 11,
                                to: 20,
                                color: 'rgba(0, 0, 0, 0)',
                                label: {
                                    text: 'Goed begin!',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            },
                            {
                                from: 21,
                                to: 50,
                                color: 'rgba(68, 170, 213, 0.1)',
                                label: {
                                    text: 'Goed bezig!',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            },
                            {
                                from: 51,
                                to: 100,
                                color: 'rgba(68, 170, 213, 0.1)',
                                label: {
                                    text: 'Straf!',
                                    style: {
                                        color: '#606060'
                                    }
                                }
                            }]
                        },
                        tooltip: {
                            valueSuffix: ' Parkings'
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
                                pointInterval: 86400000, // one hour
                                pointStart: Date.UTC(2016, 11, 17, 0, 0, 0)
                            }
                        },
                        series: [{
                            name: 'Toegevoegde parkings',
                            data: [
                                        0, 1, 5, 8, 15
                                    ]

                        }],
                        navigation: {
                            menuItemStyle: {
                                fontSize: '10px'
                            }
                        }
                    });
                });
            </script>

            <h3><u>Steden in beheer</u></h3>

            <style>
                .col-md-4 {
                    margin-top: 10px;
                }
            </style>

            <div class="row">

            @foreach($steden as $stad)

                <div class="col-md-4">
                    <div class="card card-block">
                        <h4 class="card-title">{{ $stad->stad }}</h4>
                        <p class="card-text">{{ $stad->aantal_parkings }} goedgekeurde parkings!</p>
                        {{--<a href="#" class="card-link">Card link</a>--}}
                        {{--<a href="#" class="card-link">Another link</a>--}}
                    </div>
                  </div>

            @endforeach

            </div>


        </div>
    </section>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

@endsection