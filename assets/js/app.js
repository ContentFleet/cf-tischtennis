/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
var $ = require('jquery');

jQuery(document).ready(function() {
    $('#game_users').removeAttr('multiple');


    $('.elo-history-canvas').each(function(i, obj) {
        var ctx = document.getElementById($( this ).attr('id')).getContext('2d');
        var label = $( this ).data('label');
        var labelArray = label.split(',');
        labelArray.pop();
        var value1 = $( this ).data('value1');
        var valueArray1 = value1.split(',').map(Number);
        valueArray1.pop();
        var value2 = $( this ).data('value2');
        var valueArray2 = value2.split(',').map(Number);
        valueArray2.pop();

        var config = {
            type: 'line',
            data: {
                labels: labelArray,
                datasets: [{
                    label: 'Table Tennis Elo History',
                    backgroundColor: "red",
                    borderColor: "red",
                    data: valueArray1,
                    fill: false
                },
                {
                    label: 'Billiard Elo History',
                    backgroundColor: "blue",
                    borderColor: "blue",
                    data: valueArray2,
                    fill: false
                }
                ]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Average Elo Rating / Month'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        };

        window.myLine = new Chart(ctx, config);




    });


    $('.charts_pie_win_loose').each(function(i, obj) {
        var ctx = document.getElementById($( this ).attr('id')).getContext('2d');
        var chart = new Chart(ctx, {
            // The type of chart we want to create
            type: 'pie',

            // The data for our dataset
            data: {
                datasets: [{
                    data: [
                        $( this ).data('nbwin'),
                        $( this ).data('nbloose'),
    ],
        backgroundColor: [
            "#0000FF",
            '#FF0000'
        ],
    }],

        // These labels appear in the legend and in the tooltips when hovering different arcs
        labels: [
            'Win',
            'Loose'
        ]
    }
    });
    });

    $(window).on('scroll', function(event) {
        var scrollValue = $(window).scrollTop();
        if (scrollValue == 90 || scrollValue > 90) {
            $('.navbar').addClass('fixed-top');
        }
        else{
            $('.navbar').removeClass('fixed-top');
        }
    });
});

