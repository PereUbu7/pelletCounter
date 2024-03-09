<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('bucketReduction.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);


    $bucket = 'Y-m-d';
    $debug = false;
    $merge = 1;
    $from = null;
    $to = null;

    if( $_SERVER["REQUEST_METHOD"] == "GET" )
    {
        $from = isset($_GET["from"]) ? $_GET["from"] : null;
        $to = isset($_GET["to"]) ? $_GET["to"] : null;

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

    $data = $autoRepo->GetAllSensors($bucket, $from, $to); 
?>

<!DOCTYPE html>
<html class="main-page">
    <head>
        <title>Sensor status</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="sensorErrors"  width="800" height="450"></canvas>

        <script>
            new Chart(document.getElementById("sensorErrors"), {
            responsive: true,
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "AME",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            {
                                return $item['AE'];
                            }));
                    ?>,
                        fill: false,
                        borderColor: "red",
                    },
                    {
                        label: "E1",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    {
                        return $item['DS'][0]['E'];
                    }));
                    ?>,
                        borderColor: "green",
                        fill: false
                    },
                    {
                        label: "E2",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    {
                        return $item['DS'][1]['E'];
                    }));
                    ?>,
                        borderColor: "blue",
                        fill: false
                    },
                    {
                        label: "E3",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    {
                        return $item['DS'][2]['E'];
                    }));
                    ?>,
                        borderColor: "orange",
                        fill: false
                    },
                    {
                        label: "E4",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    {
                        return $item['DS'][3]['E'];
                    }));
                    ?>,
                        borderColor: "magenta",
                        fill: false
                    },
                    {
                        label: "E5",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    {
                        return $item['DS'][4]['E'];
                    }));
                    ?>,
                        borderColor: "yellow",
                        fill: false
                    }
                ]
            },
            options: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Sensor errors'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

        </script>

    </body>

</html>