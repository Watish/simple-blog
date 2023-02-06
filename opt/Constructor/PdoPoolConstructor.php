<?php

namespace Watish\Components\Constructor;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Swoole\Coroutine;
use Watish\Components\Includes\Database;
use Watish\Components\Utils\ConnectionPool;
use Watish\Components\Utils\Logger;
use Watish\Components\Utils\PDOPool;
use Watish\Components\Utils\Table;

class PdoPoolConstructor
{
    private static Capsule $capsule;
    private static Connection $sqlConnection;

    public static function init(): void
    {
        /**
         * Init Sqlite
         */
        if(1)
        {
            PDOPool::init();
            $capsule =  new Capsule;
            $capsule->addConnection([
                'driver' => 'sqlite',
                'database' =>'./database/database.db',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],'sqlite');
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            $sqlConnection = $capsule->getConnection("sqlite");
            self::$capsule = $capsule;
            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
            self::$sqlConnection = $sqlConnection;
            Database::setSqlConnection($sqlConnection);
        }
    }

    /**
     * @return Connection
     */
    public static function getSqlConnection(): Connection
    {
        return self::$sqlConnection;
    }

    /**
     * @return Capsule
     */
    public static function getCapsule(): Capsule
    {
        return self::$capsule;
    }
}
