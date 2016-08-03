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
        $parent1 = $this->worker->random_high_quality_parent();
        $parent2 = $this->worker->random_high_quality_parent();

        $this->worker->create_children($parent1, $parent2);
    }
}