$(document).ready(function () {

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
            .click(function () {
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
            url: `http://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(cityName)}&appid=` + api_key + `&units=metric`,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
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
            error: function () {
                alert('Error fetching weather data. Please try again.');
            }
        });
    }

    function updateURL() {
        const cityNames = weatherData.map(city => city.city).join(',');
        const newURL = `${window.location.pathname}?cities=${encodeURIComponent(cityNames)}`;
        history.pushState(null, '', newURL);
    }

    $('#search-button').click(function () {
        const cityName = $('#city-search').val().trim();
        if (cityName) {
            addCity(cityName);
            $('#city-search').val('');
        }
    });

    $('#city-search').keypress(function (e) {
        if (e.which == 13) {
            $('#search-button').click();
        }
    });

    // Initial update
    updateWeatherCards(weatherData);

    // Refresh data every 5 minutes
    setInterval(function () {
        location.reload();
    }, 300000);
});