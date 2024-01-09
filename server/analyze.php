<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('manualRepo.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);
    $manualRepo = new manualRepository($config['database']['manualPelletPath']);

    $autoValues = $autoRepo->getValues('Y-m-d H:i');
    $manualValues = $manualRepo->getValues($autoValues);

    # Map number of pulses to manual records
    $groundTruth = [];
    for ($i = 0; $i < count($manualValues) - 1; ++$i) 
    {
        $currentDate = strtotime(json_decode($manualValues[$i]['value'])->date);
        $nextDate = strtotime(json_decode($manualValues[$i + 1]['value'])->date);
        $numberOfDays = ($nextDate - $currentDate) / (60 * 60 * 24);

        $numberOfBags = json_decode($manualValues[$i + 1]['value'])->antalSäckar;

        # Kgs/day
        $groundTruth[$i]['y'] = 16 * $numberOfBags / $numberOfDays;

        # Accumulate pulses given date interval of manual records
        $numberOfPulses = array_reduce(array_keys($autoValues), function ($carry, $k) use ($autoValues, $currentDate, $nextDate)
        {
            $pulseDate = strtotime($k);
            if($pulseDate >= $currentDate &&
                $pulseDate < $nextDate)    
            {
                return $carry += $autoValues[$k];
            }
            return $carry;
        },
        0);

        # #pulses
        $groundTruth[$i]['x'] = $numberOfPulses;
    }
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
        <div id="chartContainerCorr" style="height: 370px; width: 100%;"></div>
        <div id="chartContainerTimeSeries" style="height: 370px; width: 100%;"></div>

        <script>
        window.onload = function () {
        
        var chartCorr = new CanvasJS.Chart("chartContainerCorr", {
        	animationEnabled: true,
	        zoomEnabled: true,
        	title:{
        		text: "Correlation between pulses and massflow"
        	},
        	axisY: {
        		title: "kg/day"
        	},
            axisX:{      
                title: "# pulses"
            },
        	data: [{
                type: "scatter",
		        markerType: "square",
                showInLegend: true, 
                name: "requests",
                legendText: "Normal",
        		markerSize: 5,
        		dataPoints: <?php echo json_encode($groundTruth, JSON_NUMERIC_CHECK); ?>
        	}]
        });

        chartCorr.render();

        var chartTime = new CanvasJS.Chart("chartContainerTimeSeries", {
        	animationEnabled: true,
	        zoomEnabled: true,
        	title:{
        		text: "Pulses per day"
        	},
        	axisY: {
        		title: "#/day"
        	},
        	data: [{
                type: "line",
        		dataPoints: 
                <?php
                $perDayPulses = $autoRepo->getValues('Y-m-d');
                echo json_encode(array_map(function ($k) use ($perDayPulses)
                {
                    return array("y" => $perDayPulses[$k], "label" => $k);
                }, array_keys($perDayPulses)), JSON_NUMERIC_CHECK); 
                ?>
        	}]
        });

        chartTime.render();

        }
    </script>
    </body>

</html>