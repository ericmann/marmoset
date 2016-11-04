<?php

namespace EAMann\Marmoset;

class KernelRunner extends \Thread {

    private $method;
    private $params;
    private $result;
    private $joined;

    /**
     * Provide a passthrough to call_user_func_array
     *
     * @param callable $method
     * @param array    $params
     */
    public function __construct(callable $method, $params)
    {
        $this->method = $method;
        $this->params = $params;
        $this->result = null;
        $this->joined = false;
    }

    /**
     * The smallest thread in the world
     */
    public function run()
    {
        $this->result = ($this->method)(...$this->params);
    }

    /**
     * Static method to create your threads from functions ...
     *
     * @param callable $method
     * @param array    $params
     *
     * @return KernelRunner
     */
    public static function call($method, $params)
    {
        $thread = new self($method, $params);
        if($thread->start()){
            return $thread;
        }
    }

    /**
     * Do whatever, result stored in $this->result, don't try to join twice
     *
     * @return mixed
     */
    public function join()
    {
        if (!$this->joined) {
            $this->joined = true;
            parent::join();
        }

        return $this->result;
    }
}