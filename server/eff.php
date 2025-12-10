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

    function firstOrDefault(array $items, callable $predicate, $default = null)
    {
        foreach ($items as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }
        return $default;
    }

    $autoValues = $autoRepo->GetAllSensors($bucket, $from, $to);

    $manualValues = $manualRepo->getValues($autoValues);

    $consumptionValues = BucketReduction::Mean($autoValues, function ($item)
                            {
                                return (($item['DS'][2]['P50'] - $item['DS'][4]['P50'])*2700 + /* dT * 2700 l/h */
                                        ($item['DS'][3]['P50'] - $item['DS'][1]['P50'])*410) * /* dT * 410 l/h */
                                        4186 / 3600000; // Cp_water 4186 J/kg/K -> kW
                            });

    $manualTransformed = array_map(function ($item)
    {
        return [
            'timestamp' => strtotime(json_decode($item['value'])->date),
            'bags' => json_decode($item['value'])->antalSäckar
        ];
    }, $manualValues);

    $currentIntervalStartDateIndex = null;
    $currentIntervalEndDateIndex = null;
    $currentIntervalLengthSeconds = null;
    $efficiencyValues = array_fill(0, count($consumptionValues), null);

    for($i = 1; $i < count($consumptionValues); ++$i)
    {
        $pointDate = DateTime::createFromFormat($bucket, array_keys($autoValues)[$i])->getTimestamp();

        /* Find interval */
        if($currentIntervalStartDateIndex == null ||
            $pointDate >= $manualTransformed[$currentIntervalEndDateIndex]['timestamp'] ||
            $pointDate < $manualTransformed[$currentIntervalStartDateIndex]['timestamp'])
        {
            $currentIntervalStartDateIndex = firstOrDefault(array_keys($manualTransformed), function($item) use ($pointDate, $manualTransformed)
            {
                if($item == 0)
                {
                    return false;
                }

                $itemStartDate = $manualTransformed[$item - 1]['timestamp'];
                $itemEndDate = $manualTransformed[$item]['timestamp'];

                if($pointDate >= $itemStartDate &&
                    $pointDate < $itemEndDate)
                {
                    return true;
                }
            }); 

            $currentIntervalEndDateIndex = $currentIntervalStartDateIndex + 1;

            if($currentIntervalStartDateIndex === null ||
            $currentIntervalEndDateIndex > count($manualTransformed) - 1)
            {
                // No matching interval found
                continue;
            }
            $currentIntervalLengthSeconds = $manualTransformed[$currentIntervalEndDateIndex]['timestamp'] - $manualTransformed[$currentIntervalStartDateIndex]['timestamp'];
        }

        $pointDurationSeconds = ($i > 0) ? (DateTime::createFromFormat($bucket, array_keys($autoValues)[$i])->getTimestamp() -
                                        DateTime::createFromFormat($bucket, array_keys($autoValues)[$i - 1])->getTimestamp()) : 0;

        $currentNumberOfBags = $manualTransformed[$currentIntervalEndDateIndex]['bags'];
        $currentPelletEnergyUsed = 4.9 * 16 * $currentNumberOfBags; // 4.9kWh/kg * 16 kg/bag = kWh

        if($debug)
        {
            echo "Point date: " . $pointDate . "<br>";
            echo "Interval start date: " . $manualTransformed[$currentIntervalStartDateIndex]['timestamp'] . "<br>";
            echo "Interval end date: " . $manualTransformed[$currentIntervalEndDateIndex]['timestamp'] . "<br>";
            echo "Interval length seconds: " . $currentIntervalLengthSeconds . "<br>";
            echo "Point duration seconds: " . $pointDurationSeconds . "<br>";
        }

        $pointsPelletEnergyUsed = $currentPelletEnergyUsed * ($pointDurationSeconds / $currentIntervalLengthSeconds);

                             /*    kW                  *   seconds             / 3600 to get kWh  / pellet energy used in kWh */
        $efficiencyValues[$i] = $consumptionValues[$i] * $pointDurationSeconds / 3600 / $pointsPelletEnergyUsed;

        if($debug)
        {
            echo "Checking manual:<br>";
            echo "Current timestamp: " . $pointDate . "<br>";
            echo "Interval start timestamp: " . $currentIntervalStartDateIndex . "<br>";
            echo "Interval end timestamp: " . $currentIntervalEndDateIndex . "<br>";
            echo "Number of bags: " . $currentNumberOfBags . "<br>";
            echo "Current power consumption: " . $consumptionValues[$i] . "<br>";
            echo "Current efficienty: " . $efficiencyValues[$i] . "<br>";
            echo "Pellet energy: " . $currentPelletEnergyUsed . "<br>";
            echo "Pellet energy for point: " . $pointsPelletEnergyUsed . "<br>";
            echo "<br>";
        }
    }

    if($debug)
    {
        echo "<pre>";
        echo json_encode($efficiencyValues);
        echo "</pre>";

        echo "<pre>";
        echo json_encode(array_keys($autoValues));
        echo "</pre>";
    }

    // # Map number of pulses to manual records
    // for ($i = 0; $i < count($manualValues) - 1; ++$i)
    // {
    //     $currentDate = strtotime(json_decode($manualValues[$i]['value'])->date);
    //     $nextDate = strtotime(json_decode($manualValues[$i + 1]['value'])->date);
    //     $intervalLengthSeconds = $nextDate - $currentDate;

    //     $numberOfBags = json_decode($manualValues[$i + 1]['value'])->antalSäckar;

    //     $pelletEnergyUsed = 123 * $numberOfBags; // kWh

    //     # Kgs
    //     $groundTruthValue[$i]['y'] = 16 * $numberOfBags;

    //     # Accumulate pulses given date interval of manual records
    //     $eff = array_reduce(array_keys($autoValues), function ($carry, $k) use ($intervalLengthSeconds, $pelletEnergyUsed, $consumptionValues, $currentDate, $nextDate, $bucket)
    //     {
    //         $pointDate = DateTime::createFromFormat($bucket, $k)->getTimestamp();
    //         $pointDuration = $pointDate - $carry['lastTime'];

    //         echo "Key: " . $k . " Point date: " . $pointDate . " Last time: " . $carry['lastTime'] . " Duration: " . $pointDuration . "<br>";

    //         if($pointDate >= $currentDate &&
    //             $pointDate < $nextDate)
    //         {
    //             if($carry['lastTime'] != 0)
    //             {
    //                 $carry['value'] += $consumptionValues[$carry['index']]*$pointDuration / $pelletEnergyUsed / 3600;
    //             }
    //             $carry['lastTime'] = $pointDate;
    //             $carry['index'] = $carry['index'] + 1;
    //         }
    //         return $carry;
    //     },
    //     ['lastTime' => 0, 'value' => 0, 'index' => 0]);


    // }
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
            new Chart(document.getElementById("chartConsumption"), {
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
                            echo json_encode($efficiencyValues);
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