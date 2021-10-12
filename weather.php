<?php

/**
 * Weather
 * @author useful
 *
 * It contains the following functions:
 *   - getWeather($city)
 *   - getWeatherFromFile(&$city)
 *   - getWeatherFromUrl(&$city)
 *   - isFileOutdated(&$cityFile)
 */


class Weather 
{
    private $dir;
    private $settings;
    private $citySettings;
    private $conditions;
    private $windDirs;

    // конструктор
    function __construct() 
    {
        $this->dir = dirname(__FILE__).'/data/';      
        $this->settings = include('config/settings.php');
        $this->citySettings = include('config/cities.php');
        $this->conditions = include('config/conditions.php');
        $this->windDirs = include('config/winddirs.php');
    }
    
    
    // функция получает информацию о погоде для заданного города
    function getWeather($city)
    {   
        $city = strtolower($city);
        if (count($this->citySettings[$city]) == 0)
        {
            $arrayWeather = array();
            $arrayWeather['error'] = 1;
            $arrayWeather['error_value'] = $city.' is forbidden';
            echo json_encode($arrayWeather);
            return;
        }
        
        $this->getWeatherFromFile($city);
    }
    
    
    // функция получает информацию о погоде для заданного города из файла
    function getWeatherFromFile(&$city)
    {    
        $cityFile = $this->dir.$city.'.json';
        if ($this->isFileOutdated($cityFile))
        {
            $this->getWeatherFromUrl($city);
        }
        
        echo file_get_contents($cityFile);
    }
    
    
    // функция обновляет информацию о погоде для заданного города из яндекса
    function getWeatherFromUrl(&$city)
    {    
        $options = array(
                        'http' => array(
                                        'method' => "GET",
                                        'header' => $this->settings['key']."\r\n"
                                        )
                        );

        $context = stream_context_create($options);
        $file = file_get_contents($this->settings['url']."?lat=".$this->citySettings[$city]['lat']."&lon=".$this->citySettings[$city]['lon']."&lang=ru_RU", false, $context);
        //file_put_contents($this->pathFile, $file);
        
        // создать информацию для записи в файл
        $data = $text = json_decode($file);
        $arrayWeather = array();
        $arrayWeather['temp'] = $data->fact->temp;
        $arrayWeather['feels_like'] = $data->fact->temp;
        $arrayWeather['temp_water'] = $data->fact->temp_water;
        $arrayWeather['condition'] = $this->conditions[$data->fact->condition];
        $arrayWeather['wind_speed'] = $data->fact->wind_speed;
        $arrayWeather['wind_gust'] = $data->fact->wind_gust;
        $arrayWeather['wind_dir'] = $this->windDirs[$data->fact->wind_dir];
        $arrayWeather['pressure_mm'] = $data->fact->pressure_mm;
        $arrayWeather['error'] = 0;

        $cityFile = $this->dir.$city.'.json';
        file_put_contents($cityFile, json_encode($arrayWeather, JSON_UNESCAPED_UNICODE)); 
        
        
    }
    
    // функция возвращает результат устарел файл с погодой или нет
    function isFileOutdated(&$cityFile) 
    {
        if (!file_exists($cityFile)) 
        {
            return true;
        }

        $fileTime = filemtime($cityFile);
        $curTime = time();
        $difTime = ($curTime - $fileTime);
        return $difTime > $this->settings['time'];
    }
    
    
}
