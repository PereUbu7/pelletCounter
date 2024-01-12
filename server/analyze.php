<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('manualRepo.php');

    $config = parse_ini_file('appsettings.ini', true);

    $bucket = 'Y-m-d';
    $debug = false;
    $merge = 1;
    if( $_SERVER["REQUEST_METHOD"] == "GET" )
    {
	    if( !empty($_GET["bucket"]))
        {
            $bucket = $_GET["bucket"];
        }
        if( !empty($_GET['merge']))
        {
            $merge = $_GET['merge'];
        }
        if( !empty($_GET['debug']))
        {
            if($_GET['debug'] = true)
            {
                $debug = true;
            }
        }
    }

    $autoRepo = new autoRepository($config['database']['path']);
    $manualRepo = new manualRepository($config['database']['manualPelletPath']);

    $autoValues = $autoRepo->getValues($bucket);

    $manualValues = $manualRepo->getValues($autoValues);

    if($debug)
    {
        echo "Auto values: " . json_encode($autoValues) . "<br>";
    }

    # Map number of pulses to manual records
    $groundTruth = [];
    for ($i = 0; $i < count($manualValues) - 1; ++$i) 
    {
        $currentDate = strtotime(json_decode($manualValues[$i]['value'])->date);
        $nextDate = strtotime(json_decode($manualValues[$i + 1]['value'])->date);

        $numberOfBags = json_decode($manualValues[$i + 1]['value'])->antalSäckar;

        # Kgs
        $groundTruth[$i]['y'] = 16 * $numberOfBags;

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

        if($debug)
        {
            echo "Checking manual:<br>";
            echo "Current timestamp: " . $currentDate . "<br>";
            echo "Next timestamp: " . $nextDate . "<br>";
            echo "Number of bags: " . $numberOfBags . "<br>";
            echo "Kgs: " . $groundTruth[$i]['y'] . "<br>";
            echo "Number of pulses: " . $numberOfPulses . "<br><br>";
        }
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
        		text: "Correlation between pulses and mass"
        	},
        	axisY: {
        		title: "kgs"
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
        		text: "Pulses per bucket"
        	},
        	axisY: {
        		title: "#/bucket"
        	},
        	data: [{
                type: "line",
        		dataPoints: 
                <?php
                $perDayPulses = $autoRepo->getValues($bucket);
                $count = 0;
                $autoValuesMerged = array();
                $currentBucket = 0;
                foreach ($autoValues as $key => $value)
                {
                    if($debug)
                    {
                        echo "Count: " . $count . "<br>";
                        echo "Merge: " . $merge . "<br>";
                    }

                    $currentBucket = $count == 0 ? $key : $currentBucket;
                
                    $autoValuesMerged[$currentBucket] = !isset($autoValuesMerged[$currentBucket]) ? $value : $autoValuesMerged[$currentBucket] + $value;
                
                    if($debug)
                    {
                        echo "current bucket: " . $currentBucket . " with value: " . $autoValuesMerged[$currentBucket] . "<br>";
                    }
                
                    $count = ++$count % $merge;
                }
                echo json_encode(array_map(function ($k) use ($autoValuesMerged)
                {
                    return array("y" => $autoValuesMerged[$k], "label" => $k);
                }, array_keys($autoValuesMerged)), JSON_NUMERIC_CHECK); 
                ?>
        	}]
        });

        chartTime.render();

        }
    </script>
    </body>

</html>