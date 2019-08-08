<?php

namespace Janfish\Phalcon\AsyncCaller;

class Logger
{

    /**
     * Author:Robert
     *
     * @var Config
     */
    protected $_config;

    /**
     * Author:Robert
     *
     * @var bool|resource
     */
    private $_handler;

    /**
     * Logger constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->_config = $config;
        $file = $this->_config->getLogPath();
        if ($this->_config->logPath) {
            $this->_handler = fopen($file, 'ab');
        }

    }

    /**
     * Author:Robert
     *
     * @return bool
     */
    public function close(): bool
    {
        if (!$this->_handler) {
            return false;
        }
        return fclose($this->_handler);
    }

    /**
     * Author:Robert
     *
     * @param string $msg
     * @return bool|int
     */
    public function debug(string $msg): bool
    {
        if ($this->_handler) {
            return fwrite($this->_handler, $this->formatter($msg));
        }
        echo $this->formatter($msg);
        return true;
    }

    /**
     * Author:Robert
     *
     * @param string $msg
     * @return string
     */
    private function formatter(string $msg): string
    {
        return '['.date('Y-m-d H:i:s').'] '.$msg.PHP_EOL;
    }
}
