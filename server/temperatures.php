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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="chartAmbientTemp" style="height: 370px; width: 100%;"></canvas>
        <canvas id="chartAmbientHum" style="height: 370px; width: 100%;"></canvas>

        <script>
        window.onload = function () {
        
        var chartAmbientTemp = new Chart(document.getElementById("chartAmbientTemp"), {
        	animationEnabled: true,
	        zoomEnabled: true,
        	title:{
        		text: "Lufttemperatur"
        	},
        	axisY: {
        		title: "C"
        	},
            axisX:{      
                title: "tid"
            },
            type: 'area',
        	data: {
                datasets: [{
                    fill: '+2',
                    dataPoints: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return array("y" => $p['json']['ATP5'], "label" => $p['timestamp']);
                    }, $data));
                    ?>
                },
                {
        	    	dataPoints: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return array("y" => $p['json']['ATP50'], "label" => $p['timestamp']);
                    }, $data));
                    ?>
        	    },
                {
                    dataPoints: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return array("y" => $p['json']['ATP95'], "label" => $p['timestamp']);
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
        	title:{
        		text: "Luftfuktighet"
        	},
        	axisY: {
        		title: "tid"
        	},
        	data: [{
                type: "line",
        		dataPoints: 
                <?php
                echo json_encode(array_map(function ($p)
                {
                    return array("y" => $p['json']['AHP50'], "label" => $p['timestamp']);
                }, $data));
                ?>
        	}]
        });

        // chartAmbientHum.render();

        }
    </script>
    </body>

</html>