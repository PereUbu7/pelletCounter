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
        <title>Sensor status</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
      </head>

    <body>
        <canvas id="sensorErrors"  width="800" height="450"></canvas>

        <script>
            new Chart(document.getElementById("sensorErrors"), {
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
                        label: "AME",
                        data: <?php
                            echo json_encode(array_map(function ($p)
                            {
                                return $p['json']['AE'];
                            }, $data));
                    ?>,
                        fill: '2',
                        borderColor: "red",
                        backgroundColor: "rgba(179,181,198,0.5)"
                    },
                    {
                        label: "E1",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][0]['E'];
                    }, $data));
                    ?>,
                        borderColor: "green",
                        backgroundColor: "rgba(26,181,53,0.5)",
                        fill: false
                    },
                    {
                        label: "E2",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][1]['E'];
                    }, $data));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    },
                    {
                        label: "E3",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][2]['E'];
                    }, $data));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    },
                    {
                        label: "E4",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][3]['E'];
                    }, $data));
                    ?>,
                        borderColor: "blue",
                        backgroundColor: "rgba(27,42,198,0.5)",
                        fill: false
                    },
                    {
                        label: "E5",
                        data: <?php
                    echo json_encode(array_map(function ($p)
                    {
                        return $p['json']['DS'][4]['E'];
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