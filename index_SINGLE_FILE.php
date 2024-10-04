<?php

require_once 'api.php'; // Remove this when you uncomment the api_key line below and add your own API key

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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 20px 0;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 20px;
            overflow-y: auto;
            flex-grow: 1;
        }

        #search-container {
            width: 100%;
            max-width: 600px;
            margin-bottom: 20px;
        }

        #city-search {
            width: 70%;
            padding: 10px;
            font-size: 16px;
        }

        #search-button {
            width: 28%;
            padding: 10px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        #weather-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            justify-content: center;
        }

        .weather-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .weather-card h2 {
            margin-bottom: 10px;
            color: #333;
            font-size: 1.5rem;
        }

        .icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100px;
            height: 100px;
            margin: 10px 0;
        }

        .weather-icon {
            max-width: 100%;
            max-height: 100%;
        }

        .temperature {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .description {
            font-style: italic;
            margin-bottom: 15px;
        }

        .details {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
        }

        .detail {
            text-align: center;
            flex: 1 1 30%;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #666;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: bold;
        }

        .delete-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 14px;
            line-height: 24px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .delete-button:hover {
            background-color: #cc0000;
        }

        @media (min-width: 600px) {
            #weather-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 900px) {
            #weather-cards {
                grid-template-columns: repeat(3, 1fr);
            }

            .weather-card {
                height: 100%;
            }

            .temperature {
                font-size: 3rem;
            }

            .detail-value {
                font-size: 1.2rem;
            }
        }

        @media (max-height: 600px) {
            body {
                justify-content: flex-start;
            }
        }
    </style>
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
        $(document).ready(function() {
            const weatherData = <?php echo $weather_json ?: '[]'; ?>;

            function updateWeatherCards(weatherData) {
                const weatherCardsContainer = $('#weather-cards');
                weatherCardsContainer.empty();

                weatherData.forEach((city, index) => {
                    weatherCardsContainer.append(createWeatherCard(city, index));
                });
            }

            function createWeatherCard(city, index) {
                const card = $('<div>').addClass('weather-card');
                
                // Add delete button
                const deleteButton = $('<button>')
                    .addClass('delete-button')
                    .text('×')
                    .click(function() {
                        deleteCity(index);
                    });
                card.append(deleteButton);

                card.append($('<h2>').text(city.city));
                
                const iconContainer = $('<div>').addClass('icon-container');
                iconContainer.append($('<img>').addClass('weather-icon').attr('src', `http://openweathermap.org/img/wn/${city.icon}@2x.png`).attr('alt', city.description));
                card.append(iconContainer);
                
                card.append($('<div>').addClass('temperature').text(`${city.temp}°C`));
                card.append($('<div>').addClass('description').text(city.description));

                const details = $('<div>').addClass('details');
                details.append(createDetailElement('Wind', `${city.wind_speed} m/s`));
                details.append(createDetailElement('Humidity', `${city.humidity}%`));
                details.append(createDetailElement('Feels like', `${city.feels_like}°C`));

                card.append(details);
                return card;
            }

            function createDetailElement(label, value) {
                const detail = $('<div>').addClass('detail');
                detail.append($('<div>').addClass('detail-label').text(label));
                detail.append($('<div>').addClass('detail-value').text(value));
                return detail;
            }

            function deleteCity(index) {
                weatherData.splice(index, 1);
                updateWeatherCards(weatherData);
                updateURL();
            }

            function addCity(cityName) {
                $.ajax({
                    // Open Weather API Needed
                    url: `http://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(cityName)}&appid=<?php echo $api_key; ?>&units=metric`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.cod == 200) {
                            const newCity = {
                                city: data.name,
                                temp: Math.round(data.main.temp),
                                feels_like: Math.round(data.main.feels_like),
                                humidity: data.main.humidity,
                                wind_speed: data.wind.speed,
                                description: data.weather[0].description,
                                icon: data.weather[0].icon,
                            };
                            weatherData.push(newCity);
                            $('#weather-cards').append(createWeatherCard(newCity, weatherData.length - 1));
                            updateURL();
                        } else {
                            alert('City not found. Please try again.');
                        }
                    },
                    error: function() {
                        alert('Error fetching weather data. Please try again.');
                    }
                });
            }

            function updateURL() {
                const cityNames = weatherData.map(city => city.city).join(',');
                const newURL = `${window.location.pathname}?cities=${encodeURIComponent(cityNames)}`;
                history.pushState(null, '', newURL);
            }

            $('#search-button').click(function() {
                const cityName = $('#city-search').val().trim();
                if (cityName) {
                    addCity(cityName);
                    $('#city-search').val('');
                }
            });

            $('#city-search').keypress(function(e) {
                if (e.which == 13) {
                    $('#search-button').click();
                }
            });

            // Initial update
            updateWeatherCards(weatherData);

            // Refresh data every 5 minutes
            setInterval(function() {
                location.reload();
            }, 300000);
        });
    </script>
</body>
</html>
