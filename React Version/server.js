const express = require('express');
const axios = require('axios');
const cors = require('cors');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

const API_KEY = process.env.OPENWEATHERMAP_API_KEY;

app.get('/api/weather', async (req, res) => {
    const {
        cities
    } = req.query;
    const cityList = cities ? cities.split(',') : ['Oshawa', 'Toronto', 'Dhaka'];

    try {
        const weatherPromises = cityList.map(city =>
            axios.get(`http://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${API_KEY}&units=metric`)
        );

        const weatherResponses = await Promise.all(weatherPromises);
        const weatherData = weatherResponses.map(response => {
            const data = response.data;
            return {
                city: data.name,
                temp: Math.round(data.main.temp),
                feels_like: Math.round(data.main.feels_like),
                humidity: data.main.humidity,
                wind_speed: data.wind.speed,
                description: data.weather[0].description,
                icon: data.weather[0].icon,
            };
        });

        res.json(weatherData);
    } catch (error) {
        console.error('Error fetching weather data:', error);
        res.status(500).json({
            error: 'Error fetching weather data'
        });
    }
});

app.listen(port, () => {
    console.log(`Server running on port ${port}`);
});