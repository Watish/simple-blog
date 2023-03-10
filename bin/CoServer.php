<?php

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Watish\Components\Constructor\AsyncTaskConstructor;
use Watish\Components\Constructor\ClassLoaderConstructor;
use Watish\Components\Constructor\CommandConstructor;
use Watish\Components\Constructor\CrontabConstructor;
use Watish\Components\Constructor\LocalFilesystemConstructor;
use Watish\Components\Constructor\PdoPoolConstructor;
use Watish\Components\Constructor\ProcessConstructor;
use Watish\Components\Constructor\RedisPoolConstructor;
use Watish\Components\Constructor\RouteConstructor;
use Watish\Components\Constructor\ViewConstructor;
use Watish\Components\Constructor\WoopsConstructor;
use Watish\Components\Includes\Database;
use Watish\Components\Includes\Context;
use Watish\Components\Utils\ENV;
use Watish\Components\Utils\Injector\ClassInjector;
use Watish\Components\Utils\Logger;
use Watish\Components\Utils\Table;
use Watish\Components\Utils\Worker\SignalHandler;

//Define project base dir
const BASE_DIR = __DIR__ . "/../";
define("CPU_SLEEP_TIME", (1 / swoole_cpu_num()) );
define("CPU_USLEEP_TIME", (1 / swoole_cpu_num())*1000);

//Composer
require_once BASE_DIR . '/vendor/autoload.php';

//Init Local file system
LocalFilesystemConstructor::init();

//Load Env
ENV::load(BASE_DIR.'.env');

//Server Config
$server_config = require_once BASE_DIR .'/config/server.php';
define("SERVER_CONFIG", $server_config);
define("CACHE_PATH",$server_config["cache_path"]);
define("CPU_NUM",swoole_cpu_num());

//TimeZone
ini_set("date.timezone",$server_config["timezone"]);

//DatabaseExtend Config
$database_config = require_once BASE_DIR . "/config/database.php";
define("DATABASE_CONFIG",$database_config);

//Init Table
Table::init(2048,32);
Table::set("server_config",$server_config);
Table::set("database_config",$database_config);

$fileSystem = LocalFilesystemConstructor::getIlluminateFilesystem();

Logger::info("Database Check...");
if(!$fileSystem->exists("/database/database.db"))
{
    Logger::info("Init Database...");
    $dbPath = BASE_DIR.'database/database.db';
    exec("touch ".$dbPath);
    $pdo = new PDO("sqlite:{$dbPath}");
    $sql ='create table if not exists articles
            (
                article_id      integer not null
                    constraint table_name_pk
                        primary key autoincrement,
                article_title   text    not null,
                content_id      integer not null,
                author_id       integer not null,
                sort_id         integer,
                article_tags    text,
                show_switch     integer not null,
                create_time     integer not null,
                modified_time   integer not null
            );';
    $pdo->exec($sql);
    Logger::info("Table articles Init");

    $sql = 'create table if not exists users
            (
                user_id       integer not null
                    constraint users_pk
                        primary key autoincrement,
                user_name     text    not null
                    constraint user_name_uniq_index
                        unique,
                user_password text    not null,
                user_avatar   text,
                is_admin      integer,
                register_time integer not null,
                last_login    integer not null,
                token         text not null
            );';
    $pdo->exec($sql);
    Logger::info("Table users Init");

    $sql = 'create table if not exists sorts
            (
                sort_id     integer
                    constraint sorts_pk
                        primary key autoincrement,
                sort_name   text    not null
                    constraint sort_name_uniq_index
                        unique,
                create_time integer not null
            );';
    $pdo->exec($sql);
    Logger::info("Table sorts Init");

    $sql = 'create table resource
            (
                resource_id   integer not null
                    constraint resource_pk
                        primary key autoincrement,
                resource_uuid text    not null
                    constraint resource_uniq_index
                        unique,
                resource_name text    not null,
                resource_mime text    not null,
                resource_size integer not null,
                create_time   integer not null
            );';
    $pdo->exec($sql);
    Logger::info("Table resource Init");
}else{
    Logger::info("Database Ok...");
}
//Init Mysql Pool And QueryBuilder
PdoPoolConstructor::init();

//Init RedisPool
RedisPoolConstructor::init();

//Init ClassLoader and Inject
ClassLoaderConstructor::init();
//Init Injector and preCache all class loader
ClassInjector::init();

//Init Commando
CommandConstructor::init();
CommandConstructor::autoRegister();
CommandConstructor::handle();

//Task Process
AsyncTaskConstructor::init();

//Crontab Process
CrontabConstructor::init();

//Process
ProcessConstructor::init();
$pidProcessSet = ProcessConstructor::getPidProcessSet();
$processList = ProcessConstructor::getProcessList();
$processNameSet = ProcessConstructor::getProcessNameSet();

//Init Context
Context::setProcesses($processNameSet);

//Init Route
RouteConstructor::init();
$route = RouteConstructor::getRoute();

//Init Server Pool
$pool_worker_num = $server_config["worker_num"];
$pool = new Swoole\Process\Pool($pool_worker_num,1,0,true);
$pool->set(['enable_coroutine' => true]);
Context::setWorkerNum($pool_worker_num);

//Init ViewEngine
ViewConstructor::init();

//Init Woops
WoopsConstructor::init();

$pool->on('WorkerStart', function (\Swoole\Process\Pool $pool, $workerId) use ($processNameSet,$route,$pool_worker_num,$server_config) {

    $route_dispatcher = $route->get_dispatcher();

    //Init AsyncTask
    AsyncTaskConstructor::init();

    //get worker process
    $worker_process = $pool->getProcess();

    //Init Worker Process,Pool
    Context::setWorkerPool($pool);
    Context::setWorkerId($workerId);

    $server = new Server($server_config["listen_host"], $server_config["listen_port"], false , true);
    $server->set([
        'open_eof_check' => true,   //??????EOF??????
        'package_eof'    => "\r\n", //??????EOF
        'hook_flags'     => SWOOLE_HOOK_ALL
    ]);
    //Route Cache
    $route_cache = new Watish\Components\Struct\Hash\Hash();
    //Handle Request
    $server->handle('/',function (Request $request, Response $response) use ($route,$route_dispatcher,$server,$workerId,&$route_cache){
        Logger::debug("Worker #{$workerId}");
        Logger::debug($request->server["request_uri"],"Request");
        $real_path = $request->server["request_uri"];
        Logger::info($real_path);
        $request_method = $request->getMethod();
        if($route_cache->exists($real_path))
        {
            $route_info = $route_cache->get($real_path);
        }else{
            $route_info = $route_dispatcher->dispatch($request_method,$real_path);
            $route_cache->set($real_path,$route_info);
        }
        $struct_request = new \Watish\Components\Struct\Request($request,$route_info[2] ?? []);
        $struct_response = new \Watish\Components\Struct\Response($response);
        Context::setRequest($struct_request);
        Context::setResponse($struct_response);
        switch ($route_info[0])
        {
            case FastRoute\Dispatcher::NOT_FOUND:
                Context::json([
                    "Ok" => false,
                    "Msg" => "Route Not Found"
                ],404);
                Context::reset();
                return;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                Context::json([
                    "Ok" => false,
                    "Msg" => "Method Not Allowed"
                ],403);
                Context::reset();
                return;
        }
        $closure_array = $route_info[1]["route_array"];
        $closure = $closure_array["callback"];
        $before_middlewares = $closure_array["before_middlewares"];
        $global_middlewares = $route_info[1]["global_middlewares"];
        Context::setServ($server);

        //Global Middleware
        if(count($global_middlewares) > 0)
        {
            foreach ($global_middlewares as $global_middleware)
            {
                Logger::debug("GlobalMiddleware...");
                //Handle Global Middlewares
                try {
                    call_user_func_array([ClassInjector::getInjectedInstance($global_middleware),"handle"],[&$struct_request,&$struct_response]);
                }catch (Exception $exception)
                {
                    WoopsConstructor::handle($exception,"GlobalMiddleware");
                    Context::reset();
                    return;
                }
                //Check Aborted
                if(Context::isAborted())
                {
                    Logger::debug("Aborted!");
                    Context::reset();
                    return;
                }
            }
        }

        Context::setResponse($struct_response);
        Context::setRequest($struct_request);

        //Before Middleware
        if(count($before_middlewares) > 0)
        {
            foreach ($before_middlewares as $before_middleware)
            {
                Logger::debug("BeforeMiddleWare...");
                //Handle Before Middlewares
                try {
                    call_user_func_array([ClassInjector::getInjectedInstance($before_middleware),"handle"],[&$struct_request,&$struct_response]);
                }catch (Exception $exception){
                    WoopsConstructor::handle($exception,"BeforeMiddleWare");
                    Context::reset();
                    return;
                }
                //Check Aborted
                if(Context::isAborted())
                {
                    Logger::debug("Aborted!");
                    Context::reset();
                    return;
                }
            }
        }

        Context::setResponse($struct_response);
        Context::setRequest($struct_request);

        //Controller
        Logger::debug("Controller...");
        try {
            $result = call_user_func_array([ClassInjector::getInjectedInstance($closure[0]),$closure[1]],[&$struct_request,&$struct_response]);
            if(isset($result))
            {
                if(is_string($result))
                {
                    Context::html($result);
                }elseif(is_array($result))
                {
                    Context::json($result);
                }else{
                    Context::html((string)$result);
                }
            }
        }catch (Exception $exception)
        {
            WoopsConstructor::handle($exception,"Controller");
            Context::reset();
            return;
        }

        Context::reset();
    });

    //Watching Worker Process
    Coroutine::create(function() use (&$worker_process) {
        $cid = Coroutine::getuid();
        $worker_id = Context::getWorkerId();
        Logger::debug("Worker #{$worker_id} Cid #{$cid} Started");
        $socket = $worker_process->exportSocket();
        Coroutine::create(function ()use ($socket){
            while (1)
            {
                $rec = $socket->recv();
                if($rec)
                {
                    try{
                        (new SignalHandler($rec))->handle();
                    }catch (Exception $e)
                    {
                        Logger::error($e->getMessage());
                    }
                }
                Coroutine::sleep(CPU_SLEEP_TIME);
            }
        });
    });
    Context::setServ($server);
    $server->start();
});
//Logger::clear();
//Logger::CLImate()->bold()->white()->addArt(BASE_DIR."/storage/Framework")->draw("Logo");
Logger::info("Server Started...");
$pool->start();
