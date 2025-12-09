<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('bucketReduction.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);

    $bucket = 'Y-m-d H:i';
    $debug = false;
    $merge = 1;
    $from = (new DateTime('-1 week'))->format('Y-m-d H:i'); 
    $to = date('Y-m-d H:i');

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

    $data = $autoRepo->GetAllSensors($bucket, $from, $to);
?>

<!DOCTYPE html>
<html class="main-page">
    <head>
        <title>Temperatures</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="chartAmbientTemp"  width="800" height="450"></canvas>
        <canvas id="chartAmbientHum"  width="800" height="450"></canvas>
        <canvas id="Temp1"  width="800" height="450"></canvas>
        <canvas id="Temp3"  width="800" height="450"></canvas>
        <canvas id="Temp35diff"  width="800" height="450"></canvas>
        <canvas id="Temp4"  width="800" height="450"></canvas>
        <canvas id="Temp42diff"  width="800" height="450"></canvas>
        <canvas id="chartAnalysis"  width="800" height="450"></canvas>

        <script>
            new Chart(document.getElementById("chartAmbientTemp"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['ATP5'];
                            }));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['ATP50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['ATP95'];
                    }));
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
                    text: 'Lufttemperatur'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("chartAmbientHum"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['AHP5'];
                            }));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['AHP50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['AHP95'];
                    }));
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
                    text: 'Luftfuktighet'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("Temp1"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][0]['P5'];
                            }));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][0]['P50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][0]['P95'];
                    }));
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
                    text: 'Inkommande kallvattentemperatur'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("Temp3"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5 1",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][2]['P5'];
                            }));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50 1",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][2]['P50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95 1",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][2]['P95'];
                    }));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    },
                    {
                        label: "P5 2",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][4]['P5'];
                            }));
                    ?>,
                        fill: '5',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50 2",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][4]['P50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95 2",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][4]['P95'];
                    }));
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
                    text: 'Systemvattentemperatur'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("Temp35diff"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][2]['P5'] - $item['DS'][4]['P95']; 
                            }));
                        ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][2]['P50'] - $item['DS'][4]['P50']; 
                            }));
                        ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][2]['P95'] - $item['DS'][4]['P5']; 
                            }));
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
                    text: 'Systemvattentemperatur differens'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("Temp4"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5 1",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][3]['P5'];
                            }));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50 1",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][3]['P50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95 1",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][3]['P95'];
                    }));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    },
                    {
                        label: "P5 2",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][1]['P5'];
                            }));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50 2",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][1]['P50'];
                    }));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95 2",
                        data: <?php
                    echo json_encode(BucketReduction::Mean($data, function ($item) 
                    { 
                        return $item['DS'][1]['P95'];
                    }));
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
                    text: 'Tappvattentemperatur'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("Temp42diff"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P5",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][3]['P5'] - $item['DS'][1]['P95']; 
                            }));
                        ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][3]['P50'] - $item['DS'][1]['P50']; 
                            }));
                        ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return $item['DS'][3]['P95'] - $item['DS'][1]['P5']; 
                            }));
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
                    text: 'Tappvattentemperatur differens'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                },
                animation: false
            }
            });

            new Chart(document.getElementById("chartAnalysis"), {
            type: 'line',
            data: {
                labels: <?php
                    echo json_encode(array_keys($data));
                    ?>,
                datasets: [
                    {
                        label: "P50",
                        data: <?php
                            echo json_encode(BucketReduction::Mean($data, function ($item) 
                            { 
                                return (($item['DS'][2]['P50'] - $item['DS'][4]['P50'])*2400 + /* dT * 2400 l/h */ 
                                        ($item['DS'][3]['P50'] - $item['DS'][1]['P50'])*400) * /* dT * 400 l/h */
                                        4186 / 3600000; // Cp_water 4186 J/kg/K -> kW
                            }));
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