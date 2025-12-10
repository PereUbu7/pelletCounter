<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('manualRepo.php');
    require_once('bucketReduction.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);
    $manualRepo = new manualRepository($config['database']['manualPelletPath']);

    $bucket = 'Y-m-d H';
    $debug = false;
    $merge = 1;
    $from = (new DateTime('-1 week'))->format('Y-m-d H'); 
    $to = date('Y-m-d H');

    if( $_SERVER["REQUEST_METHOD"] == "GET" )
    {
        $from = isset($_GET["from"]) ? $_GET["from"] : $from;
        $to = isset($_GET["to"]) ? $_GET["to"] : $to;

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
            if($_GET['debug'] == true)
            {
                $debug = true;
            }
        }
    }

    $autoValues = $autoRepo->GetAllSensors($bucket, $from, $to);

    $manualValues = $manualRepo->getValues($autoValues);

    $consumptionValues = BucketReduction::Mean($autoValues, function ($item) 
                            { 
                                return (($item['DS'][2]['P50'] - $item['DS'][4]['P50'])*2700 + /* dT * 2700 l/h */ 
                                        ($item['DS'][3]['P50'] - $item['DS'][1]['P50'])*410) * /* dT * 410 l/h */
                                        4186 / 3600000; // Cp_water 4186 J/kg/K -> kW
                            });

    # Map number of pulses to manual records
    for ($i = 0; $i < count($manualValues) - 1; ++$i) 
    {
        $currentDate = strtotime(json_decode($manualValues[$i]['value'])->date);
        $nextDate = strtotime(json_decode($manualValues[$i + 1]['value'])->date);
        $intervalLengthSeconds = $nextDate - $currentDate;

        $numberOfBags = json_decode($manualValues[$i + 1]['value'])->antalSäckar;

        $pelletEnergyUsed = 123 * $numberOfBags; // kWh

        # Kgs
        $groundTruthValue[$i]['y'] = 16 * $numberOfBags;

        # Accumulate pulses given date interval of manual records
        $eff = array_reduce(array_keys($consumptionValues), function ($carry, $k) use ($intervalLengthSeconds, $pelletEnergyUsed, $consumptionValues, $currentDate, $nextDate)
        {
            $pointDate = strtotime($k);
            $pointDuration = $pointDate - $carry['lastTime'];

            echo "Point date: " . $pointDate . " Last time: " . $carry['lastTime'] . " Duration: " . $pointDuration . "<br>";

            if($pointDate >= $currentDate &&
                $pointDate < $nextDate)    
            {
                if($carry['lastTime'] != 0)
                {
                    $carry['value'] += $consumptionValues[$k]*$pointDuration / $pelletEnergyUsed / 3600;
                }
                $carry['lastTime'] = $pointDate;
            }
            return $carry;
        },
        ['lastTime' => 0, 'value' => 0]);

        if($debug)
        {
            echo "Checking manual:<br>";
            echo "Current timestamp: " . $currentDate . "<br>";
            echo "Next timestamp: " . $nextDate . "<br>";
            echo "Number of bags: " . $numberOfBags . "<br>";
            echo "Eff: " . $eff[$i]['value'] . "<br>";
            echo "Pellet energy: " . $pelletEnergyUsed . "<br><br>";
        }
    }
?>

<!DOCTYPE html>
<html class="main-page">
    <head>
        <title>Effektivitet</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="chartConsumption"  width="800" height="450"></canvas>
        <canvas id="chartEfficiency"  width="800" height="450"></canvas>

        <script>
            new Chart(document.getElementById("chartAnalysis"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($autoValues));
                    ?>,
                datasets: [
                    {
                        label: "P50",
                        data: <?php
                            echo json_encode($consumptionValues);
                    ?>,
                        fill: '2',
                        borderColor: "green",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    }
                ]
            },
            options: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Effektiv förbrukning kW'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("chartEfficiency"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($autoValues));
                    ?>,
                datasets: [
                    {
                        label: "P50",
                        data: <?php
                            echo array_map(function ($v) { return $v['value']; }, $eff);
                    ?>,
                        fill: '2',
                        borderColor: "green",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    }
                ]
            },
            options: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Effektiv förbrukning kW'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });
        </script>
    </body>
</html>