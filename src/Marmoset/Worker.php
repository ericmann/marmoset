<?php
/*
 * This file is part of the Marmoset project.
 *
 * (c) Eric Mann <eric@eamann.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EAMann\Marmoset;

class Worker extends \Worker
{
    protected $population;

    protected $sum;

    protected $max;

    public $children = [];

    public function __construct( $population, $sum, $max )
    {
        require_once( __DIR__ . '/../../vendor/autoload.php' );

        $this->population = $population;
        $this->sum = $sum;
        $this->max = $max;
    }

    /**
     * Execute the thread once it's started up
     */
    public function run()
    {

    }

    /**
     * Select a parent at random, based on their fitness.
     *
     * @return string
     */
    public function random_high_quality_parent() {
        $val = random_float() * $this->sum;

        for ($i = 0; $i < count($this->population); $i++) {
            $maxMinusFitness = $this->max - fitness($this->population[ $i ]);
            if ($val < $maxMinusFitness) {
                return $this->population[ $i ];
            }
            $val -= $maxMinusFitness;
        }
    }

    public function create_children($parent1, $parent2) {
        foreach( create_children($parent1, $parent2) as $child ) {
            $this->children[] = $child;
        }
    }
}