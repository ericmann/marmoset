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

    public function run()
    {
        // Get parents
        $parent1 = $this->random_high_quality_parent();
        $parent2 = $this->random_high_quality_parent();

        $this->worker->addChildren(create_children($parent1, $parent2));
    }

    /**
     * Select a parent at random, based on their fitness.
     *
     * @return string
     */
    public function random_high_quality_parent() {
        $val = random_float() * $this->worker->sum;

        for ($i = 0; $i < count($this->worker->population); $i++) {
            $maxMinusFitness = $this->worker->max - fitness($this->worker->population[ $i ]);
            if ($val < $maxMinusFitness) {
                return $this->worker->population[ $i ];
            }
            $val -= $maxMinusFitness;
        }
    }
}