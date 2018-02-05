<?php
use Workerman\Worker;
require_once 'autoload.php';
use Workerman\Lib\Timer;

class MysqlPool
{
    private $instance;
    private $busy;
    private $connect;
    public function __construct(){}
    public function createPDO()
    {
        if(!empty($this->instance)) {
            return $this->instance;
        } else {
            $this->instance = new  PDO('mysql:host=localhost;dbname:wifi_ad', 'root', 'root');
        }
    }
    public function onWorkerStart($worker)
    {
        echo $worker->id."\r\n";
        $time_interval = 2;
        Timer::add($time_interval, function() {
            echo "task run\n";
        });
        return $this->createPDO();
    }
    public function onConnect($connection)
    {
        $this->connect++;
        if ($this->connect >= 100) {
            $this->busy = true;
        } else {
            $connection->send("连接太多，请等待连接释放!");
        }
    }
    public function onMessage($connection, $message)
    {

    }
    public function onClose($connection)
    {
        $this->busy = 0;
    }
    public function onWorkerStop($connection){}
}

$worker = new Worker('tcp://0.0.0.0:8484');
$worker->count = 20;
$pool =  new MysqlPool();
$worker->onWorkerStart = array($pool, "onWorkerStart");
Worker::runAll();