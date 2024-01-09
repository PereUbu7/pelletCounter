<?php
    ini_set('display_errors', 1);
    require_once('autoRepo.php');
    require_once('manualRepo.php');

    $config = parse_ini_file('appsettings.ini', true);

    $autoRepo = new autoRepository($config['database']['path']);
    $manualRepo = new manualRepository($config['database']['manualPelletPath']);

    $autoValues = $autoRepo->getValues();
    $manualValues = $manualRepo->getValues($autoValues);
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
        <?php
            echo json_encode($autoValues);
        ?>
    </body>

</html>