<?php
 /***********************************************
         **  @ Author: Useful
         **  @ 2021
         **  API получения информации о погоде
  *************************************************/

    // получить action
    $action = (string)filter_input(INPUT_GET, 'action');

    if ($action == "weather")
    {
        require_once('weather.php');
        $weather = new Weather();
        $weather->getWeather((string)filter_input(INPUT_GET, 'city'));
    }
    
        
?>