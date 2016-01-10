$(function () {
    $('#container').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Beschikbare parking'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            name: 'Waarde',
            colorByPoint: true,
            data: [{
                name: 'Beschikbaar',
                y: 100 - ((bezet/totaal) *100)
            }, {
                name: 'Bezet',
                y: (bezet/totaal) *100,
                sliced: true,
                selected: true
            }]
        }]
    });
});