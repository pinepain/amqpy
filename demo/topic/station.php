<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/24/12 8:16 PM
 */

include (dirname(__FILE__) . '/../config.php');


use AMQPy\Solutions\Generic;

class DemoStation {
    public $continents = array(
        'africa',
        'europe',
    );

    public $terms = array(
        'weekly',
        'daily',
    );

    public function getWeekly($continent) {
        $continent_temperature = 'get' . ucfirst($continent) . 'Temperature';

        return "{$this->$continent_temperature()} weather every week in {$continent}";
    }

    public function getDaily($continent) {
        $continent_temperature = 'get' . ucfirst($continent) . 'Temperature';

        return "{$this->$continent_temperature()} weather every day in {$continent}";
    }

    private function getAfricaTemperature() {
        return 'warm';
    }

    private function getEuropeTemperature() {
        return 'cold';
    }

}

$exchange = new Generic('example.topic.weather', Config::get('amqp_params'));

$forecast = new DemoStation();

$count = 0;

while (true) {

    $continent = $forecast->continents[array_rand($forecast->continents)];
    $term      = $forecast->terms[array_rand($forecast->terms)];


    $method    = 'get' . ucfirst($term);
    $weather   = $forecast->$method($continent);

    $exchange->send($weather, "example.topic.weather.{$continent}.{$term}");
    echo "Sent {$continent} {$term} forecast #{$count}: {$weather}" . PHP_EOL;

    $count++;
    usleep(500000);
}


