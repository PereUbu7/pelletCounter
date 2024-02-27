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

        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
      </head>

    <body>
        <div id="chartAmbientTemp" style="height: 370px; width: 100%;"></div>
        <div id="chartAmbientHum" style="height: 370px; width: 100%;"></div>

        <script>
        window.onload = function () {
        
        var chartCorr = new CanvasJS.Chart("chartAmbientTemp", {
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
        	data: [{
                type: "scatter",
		        markerType: "square",
                showInLegend: true, 
                name: "requests",
                legendText: "Normal",
        		markerSize: 5,
        		dataPoints: <?php
                json_encode(array_map(function ($p)
                {
                    return array("y" => $p['json']['ATP50'], "label" => $p['timestamp']);
                }, $data));
                ?>
        	}]
        });

        chartCorr.render();

        var chartTime = new CanvasJS.Chart("chartAmbientHum", {
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
                json_encode(array_map(function ($p)
                {
                    return array("y" => $p['json']['AHP50'], "label" => $p['timestamp']);
                }, $data));
                ?>
        	}]
        });

        chartTime.render();

        }
    </script>
    </body>

</html>