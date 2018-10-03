<?php
     file_put_contents("price.csv", fopen("https://etherscan.io/chart/etherprice?output=csv", 'r'));
     file_put_contents("count.csv", fopen("https://etherscan.io/chart/address?output=csv", 'r'));
 ?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Google Graph and CSV</title>
    <meta name="description" content="test">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js" crossorigin="anonymous"> </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-csv/0.8.9/jquery.csv.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <div id="chart_div" style="width: 500px; height: 300px;"></div>
    <script>
        google.charts.load('current', {
                packages: ['corechart', 'line']
            }

        );
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            $.get("price.csv", function(csvString) {
                var csv_price_arr = $.csv.toArrays(csvString, {
                    onParseValue: $.csv.hooks.castToScalar
                });																			   //Convert csv to Array
                var data_price = new google.visualization.arrayToDataTable(csv_price_arr);     //Set the Ether Price data table
                $.get("count.csv", function(csvString) {
                    var csv_count_arr = $.csv.toArrays(csvString, {
                        onParseValue: $.csv.hooks.castToScalar
                    });																		   //Convert csv to Array
                    for (var i = csv_count_arr.length - 1; i >= 1; i--) {
                        csv_count_arr[i][2] = csv_count_arr[i][2] - csv_count_arr[i - 1][2];
                    }
                    csv_count_arr[1][2] = 0;
                    var data_count = new google.visualization.arrayToDataTable(csv_count_arr);  //Set the Address Count data table
                    var joinedData = google.visualization.data.join(data_count, data_price, 'inner', [
                        [0, 0],
                        [1, 1]
                    ], [2], [2]);					//Join the two Datatables
                    var view = new google.visualization.DataView(joinedData);
                    view.setColumns([{
                        calc: function(dt, row) {
                            return new Date(dt.getValue(row, 0));
                        },
                        label: 'Date',
                        type: 'date'
                    }, 2, 3]); 								 //Select certain columns from joined data and Convert the date Format to chart readable		
                    var final_table = view.toDataTable(); 	 //Sort Data according to date ASC
                    final_table.sort([{
                        column: 0
                    }]);
                    final_table.setColumnLabel(0, "Date");   //Set Column Labels
                    final_table.setColumnLabel(1, "New Address Count");
                    final_table.setColumnLabel(2, "Ether Price");
                    var options = {
                        width: 900,
                        height: 500,
                        min: 0,
                        crosshair: {
                            trigger: 'both'
                        },
                        interpolateNulls: false,
                        series: {
                            0: {
                                targetAxisIndex: 0
                            },
                            1: {
                                targetAxisIndex: 1
                            }
                        },
                        vAxes: {
                            0: {
                                title: 'New Address Count',
                                gridlines: {
                                    color: 'transparent'
                                }
                            },
                            1: {
                                title: 'Ether Price',
                                gridlines: {
                                    color: 'transparent'
                                }

                            }
                        },
                        explorer: {
                            axis: 'horizontal',
                            keepInBounds: true,
                            maxZoomIn: 4.0
                        },
                        vAxis: {
                            minValue: 0
                        },
                        hAxis: {
                            gridlines: {
                                color: 'transparent'
                            }
                        },
                        strictFirstColumnType: false,
                    };
                    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                    chart.draw(final_table, options);
                })
            })
        }
    </script>