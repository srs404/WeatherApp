<?php

require_once '../api.php'; // Remove this when you uncomment the api_key line below and add your own API key

error_reporting(E_ALL);
ini_set('display_errors', 1);


// $api_key = 'OPEN_WEATHER_API_KEY_NEEDS_TO_BE_HERE';

// Default cities to display
$default_cities = ['Oshawa', 'Toronto', 'Dhaka'];
$units = 'metric';

$cities = isset($_GET['cities']) ? explode(',', $_GET['cities']) : $default_cities;

$weather_data = [];

foreach ($cities as $city) {
    $url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid={$api_key}&units={$units}";
    $response = @file_get_contents($url);
    if ($response === FALSE) {
        continue;
    }
    $data = json_decode($response, true);

    if ($data && $data['cod'] == 200) {
        $weather_data[] = [
            'city' => $data['name'],
            'temp' => round($data['main']['temp']),
            'feels_like' => round($data['main']['feels_like']),
            'humidity' => $data['main']['humidity'],
            'wind_speed' => $data['wind']['speed'],
            'description' => $data['weather'][0]['description'],
            'icon' => $data['weather'][0]['icon'],
        ];
    }
}

$weather_json = json_encode($weather_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="search-container">
        <input type="text" id="city-search" placeholder="Enter city name">
        <button id="search-button">Search</button>
    </div>
    <div class="container">
        <div id="weather-cards"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        const weatherData = <?php echo $weather_json ? : '[]'; ?> ;
        const api_key = '<?php echo $api_key; ?>';
    </script>
    <script src="script.js"></script>
</body>
</html>