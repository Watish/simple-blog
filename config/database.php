<?php

use Watish\Components\Utils\ENV;

return  [
    "redis" => [
        "enable" => ENV::getConfig("Redis")["REDIS_ENABLE"] == "1",
        "parameters" => [
            "host" => ENV::getConfig("Redis")["REDIS_HOST"],
            "port" => (int)ENV::getConfig("Redis")["REDIS_PORT"],
            "database" => (int)ENV::getConfig("Redis")["REDIS_DATABASE"]
        ],
        "options" => [
            "prefix" => ENV::getConfig("Redis")["REDIS_PREFIX"],
        ],
        "pool_max_count" => swoole_cpu_num()*10,
        "pool_min_count" => swoole_cpu_num()
    ],
];
