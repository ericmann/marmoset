<?php

namespace EAMann\Marmoset;

class KernelRunner extends \Thread {

    private   $population;
    private   $sumOfMaxMinusFitness;
    private   $maxFitness;
    protected $reducer;
    private   $result;

    /**
     * Provide a passthrough to call_user_func_array
     *
     * @param array    $population
     * @param float    $sumOfMaxMinusFitness
     * @param float    $maxFitness
     * @param callable $reducer
     */
    public function __construct($population, $sumOfMaxMinusFitness, $maxFitness, $reducer)
    {
        $this->population = $population;
        $this->sumOfMaxMinusFitness = $sumOfMaxMinusFitness;
        $this->maxFitness = $maxFitness;
        $this->reducer = $reducer;
    }

    /**
     * The smallest thread in the world
     */
    public function run()
    {
        $this->synchronized(function() {
            $this->result = (array) ($this->reducer)($this->population, $this->sumOfMaxMinusFitness, $this->maxFitness);
            $this->notify();
        });
    }

    /**
     * Static method to create your threads from functions ...
     *
     * @param array    $population
     * @param float    $sumOfMaxMinusFitness
     * @param float    $maxFitness
     * @param callable $reducer
     *
     * @return KernelRunner
     */
    public static function call($population, $sumOfMaxMinusFitness, $maxFitness, $reducer)
    {
        $thread = new self($population, $sumOfMaxMinusFitness, $maxFitness, $reducer);
        if($thread->start()){
            return $thread;
        }
    }

    public function getResult() {
        return $this->synchronized(function(){
            while (!$this->result)
                $this->wait();
            return $this->result;
        });
    }
}