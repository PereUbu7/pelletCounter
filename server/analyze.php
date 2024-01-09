<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('manualRepo.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);
    $manualRepo = new manualRepository($config['database']['manualPelletPath']);

    $autoValues = $autoRepo->getValues();
    $manualValues = $manualRepo->getValues($autoValues);

    echo json_encode($manualValues);

    $groundTruth = array();
    for ($i = 0; $i < count($manualValues) - 1; ++$i) 
    {
        $currentDate = strtotime(json_decode($manualValues[$i]['value'])->date);
        $nextDate = strtotime(json_decode($manualValues[$i + 1]['value'])->date);
        $numberOfDays = ($nextDate - $currentDate) / (60 * 60 * 24);

        $numberOfBags = json_decode($manualValues[$i + 1]['value'])->antalSäckar;

        $groundTruth[$i]['kgsPerDay'] = 16 * $numberOfBags / $numberOfDays;

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

        $groundTruth[$i]['numberOfPulses'] = $numberOfPulses;
    }
?>

<!DOCTYPE html>
<html class="main-page">
    <head>
        <title>Analyze</title>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta name="viewport" content="width=device-width, initial-scale=1">
      </head>
    
    <?php
// "http://" + window.location.hostname + "/logAnything/api.php"; + "?key=pellets"
    ?>

    <body>
        <p>
        <?php
            echo json_encode($autoValues);
        ?>
        </p>
        </br>
        <p>
        <?php
            echo json_encode($groundTruth);
        ?>
        </p>
    </body>

</html>