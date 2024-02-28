<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);

    $data = $autoRepo->GetAllSensors();
?>

<!DOCTYPE html>
<html class="main-page">
    <head>
        <title>Analyze</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="chartAmbientTemp"  width="800" height="450"></canvas>
        <!-- <canvas id="chartAmbientHum"  width="800" height="450"></canvas> -->

        <script>
            new Chart(document.getElementById("chartAmbientTemp"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['timestamp'];
                    }, $data));
                    ?>,
                datasets: [
                    {
                        label: "P5",
                        data: <?php
                            echo json_encode(array_map(function ($p)
                            {
                                return $p['json']['ATP5'];
                            }, $data));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['ATP50'];
                    }, $data));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['ATP95'];
                    }, $data));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    }
                ]
            },
            options: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Blaj'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });
        </script>
<!-- 
        <script>
        window.onload = function () {
        
        var chartAmbientTemp = new Chart(document.getElementById("chartAmbientTemp"), {
        	animationEnabled: true,
	        zoomEnabled: true,
        	options: {
                title: {
                    display: true,
                    text: 'Lufttemperatur'
                }
            },
            type: 'area',
        	data: {
                labels: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['timestamp'];
                    }, $data));
                    ?>,
                datasets: [{
                    fill: '+2',
                    dataPoints: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['ATP5'];
                    }, $data));
                    ?>
                },
                {
        	    	dataPoints: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['ATP50'];
                    }, $data));
                    ?>
        	    },
                {
                    dataPoints: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['ATP95'];
                    }, $data));
                    ?>
                }
            ]
        }
        });

        // chartAmbientTemp.render();

        var chartAmbientHum = new Chart(document.getElementById("chartAmbientHum"), {
        	animationEnabled: true,
	        zoomEnabled: true,
        	options: {
                title: {
                    display: true,
                    text: 'Luftfuktighet'
                }
            },
        	data: {
                type: "line",
                labels: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['timestamp'];
                    }, $data));
                    ?>,
        		datasets: [{
                <?php
                echo json_encode(array_map(function ($p)
                {
                    return $p['json']['AHP50'];
                }, $data));
                ?>
                }]
        	}
        });

        // chartAmbientHum.render();

        }
    </script> -->
    </body>

</html>