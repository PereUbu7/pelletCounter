<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');

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
        <title>Temperatures</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="chartAmbientTemp"  width="800" height="450"></canvas>
        <canvas id="chartAmbientHum"  width="800" height="450"></canvas>
        <canvas id="Temp1"  width="800" height="450"></canvas>
        <canvas id="Temp2"  width="800" height="450"></canvas>
        <canvas id="Temp3"  width="800" height="450"></canvas>
        <canvas id="Temp35diff"  width="800" height="450"></canvas>
        <canvas id="Temp4"  width="800" height="450"></canvas>

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
                    text: 'Lufttemperatur'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

            new Chart(document.getElementById("chartAmbientHum"), {
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
                                return $p['json']['AHP5'];
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
                        return $p['json']['AHP50'];
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
                        return $p['json']['AHP95'];
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
                    text: 'Luftfuktighet'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

            new Chart(document.getElementById("Temp1"), {
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
                                return $p['json']['DS'][0]['P5'];
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
                        return $p['json']['DS'][0]['P50'];
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
                        return $p['json']['DS'][0]['P95'];
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
                    text: 'Temperatur 1'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

            new Chart(document.getElementById("Temp2"), {
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
                                return $p['json']['DS'][1]['P5'];
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
                        return $p['json']['DS'][1]['P50'];
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
                        return $p['json']['DS'][1]['P95'];
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
                    text: 'Temperatur 2'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

            new Chart(document.getElementById("Temp3"), {
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
                        label: "P5 1",
                        data: <?php
                            echo json_encode(array_map(function ($p)
                            {
                                return $p['json']['DS'][2]['P5'];
                            }, $data));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50 1",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][2]['P50'];
                    }, $data));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95 1",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][2]['P95'];
                    }, $data));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    },
                    {
                        label: "P5 2",
                        data: <?php
                            echo json_encode(array_map(function ($p)
                            {
                                return $p['json']['DS'][4]['P5'];
                            }, $data));
                    ?>,
                        fill: '5',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "P50 2",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][4]['P50'];
                    }, $data));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "P95 2",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][4]['P95'];
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
                    text: 'Temperatur 3-5'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

            new Chart(document.getElementById("Temp35diff"), {
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
                                return $p['json']['DS'][2]['P5'] - $p['json']['DS'][4]['P95'];
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
                        return $p['json']['DS'][2]['P50'] - $p['json']['DS'][4]['P50'];
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
                        return $p['json']['DS'][2]['P95'] - $p['json']['DS'][4]['P5'];
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
                    text: 'Temperatur 3-5 differens'
                },
                elements: {
                    line: {
                        tension: 0
                    }
                }
            }
            });

            new Chart(document.getElementById("Temp4"), {
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
                                return $p['json']['DS'][3]['P5'];
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
                        return $p['json']['DS'][3]['P50'];
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
                        return $p['json']['DS'][3]['P95'];
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
                    text: 'Temperatur 4'
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