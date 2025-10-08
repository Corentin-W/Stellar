<?php

return [
    'latitude' => (float) env('OBSERVATORY_LATITUDE', 0.0),
    'longitude' => (float) env('OBSERVATORY_LONGITUDE', 0.0),
    'altitude' => (float) env('OBSERVATORY_ALTITUDE', 0.0),
    'timezone' => env('OBSERVATORY_TIMEZONE', config('app.timezone', 'UTC')),
];
