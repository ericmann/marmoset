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

class Job extends \Threaded
{
    public function __construct($population, $sum, $max)
    {
        $this->population = $population;
        $this->sum = $sum;
        $this->max = $max;
    }

    public function run()
    {
        // Get parents
        $parent1 = $this->random_high_quality_parent();
        $parent2 = $this->random_high_quality_parent();

        $this->test = "tst";
        $children = create_children($parent1, $parent2);
        $this->child1 = $children[0];
        $this->child2 = $children[1];
        $this->setGarbage();
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
}