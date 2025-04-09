<?php 
namespace Eleadtech\Bcore\Logger;
use \Psr\Log\LoggerInterface;

class Logger 
{
    protected $log;
    protected $handle;
    public function __construct(
        
        LoggerInterface $logger
    ) {
        $this->log = $logger;
        
    }
    public function writeLog($message,$relativePath){
        
        if(!$this->handle){
            $this->handle = new \Monolog\Handler\StreamHandler($relativePath);
            $this->log->pushHandler($this->handle);
        }
        $this->log->info($message);
    }
}
