<?php
/*
 * This file is part of the Marmoset project.
 *
 * (c) Eric Mann <eric@eamann.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EAMann\Marmoset\Command;

use EAMann\Marmoset;
use EAMann\Marmoset\Console\Status;
use Symfony\Component\Console\Command\Command as SCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The actual wiring for the Symfony command.
 *
 * @package EAMann\Marmoset
 */
class Command extends SCommand
{
    /**
     * @var Status
     */
    protected $status;

    /**
     * @var array
     */
    protected $population;

    /**
     * Configure the CLI command we're going to run
     */
    protected function configure()
    {
        $this->setName('run')
             ->setDescription('Make the monkeys type')
             ->addOption(
                 'mode',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'Run-mode',
                 'synchronous'
             );
    }

    /**
     * Update the status display.
     *
     * @param int $generation
     * @param string $best
     */
    protected function updateStatus(int $generation, string $best)
    {
        $this->status->setGeneration($generation)->setBest($best);
    }
    
    /**
     * Create a new generation given the existing entities and using their relative fitnesses to
     * determine which monkeys are allowed to breed.
     *
     * @return array
     */
    protected function create_next_generation()
    {
        // Max fitness
        $maxFitness = array_reduce($this->population, function(float $max, string $current) {
            return max($max, Marmoset\fitness($current));
        }, 0) + 1.0;

        // Sum of max minus fitness
        $sumOfMaxMinusFitness = array_reduce($this->population, function(float $carry, string $current) use ($maxFitness) {
            return $carry + ($maxFitness - Marmoset\fitness($current));
        }, 0);

        $newPop = [];
        if (getenv('ASYNC')) {

            $pool = new \Pool(8, 'EAMann\Marmoset\Worker', [$this->population, $sumOfMaxMinusFitness, $maxFitness]);
            $threads = [];


            foreach (range(1, count($this->population) / 2) as $counter) {
                $threaded = new Marmoset\Job();
                $threads[] = $threaded;
                $pool->submit($threaded);
            }


            while($pool->collect(function($job) use (&$newPop) {
                if ( $job->isGarbage() ) {
                    foreach( $job->worker->children as $child ) {
                        $newPop[] = $child;
                    }
                }
                return $job->isGarbage();
            })) continue;

            $pool->shutdown();

            $newPop = array_slice( $newPop, 0, 30 );
        } else {
            foreach (range(1, count($this->population) / 2) as $counter) {
                $parent1 = $this->random_high_quality_parent($sumOfMaxMinusFitness, $maxFitness);
                $parent2 = $this->random_high_quality_parent($sumOfMaxMinusFitness, $maxFitness);
                $newPop = array_merge($newPop, Marmoset\create_children($parent1, $parent2));
            }
        }

        return $newPop;
    }

    /**
     * Select a parent at random, based on their fitness
     *
     * @param float $sum Sum of (max fitness - fitness) for all potential parents
     * @param float $max Max fitness across all potential parents
     *
     * @return string
     */
    protected function random_high_quality_parent(float $sum, float $max)
    {
        $val = Marmoset\random_float() * $sum;

        for ($i = 0; $i < count($this->population); $i++) {
            $maxMinusFitness = $max - Marmoset\fitness($this->population[ $i ]);
            if ($val < $maxMinusFitness) {
                return $this->population[ $i ];
            }
            $val -= $maxMinusFitness;
        }
    }

    /**
     * Get the best member of the generation
     *
     * @return string
     */
    protected function best()
    {
        return array_reduce($this->population, function (string $best, string $current) {
            if (Marmoset\fitness($current) < Marmoset\fitness($best)) {
                return $current;
            }

            return $best;
        }, "");
    }

    /**
     * Actually execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->status = new Status($output);

        // Set initial state
        $generation = 0;
        $bestGenome = null;

        $this->population = Marmoset\random_population(30, strlen(Marmoset\TARGET));

        $running = true;
        do {
            // Move to the next generation
            $generation += 1;
            $this->population = $this->create_next_generation();

            // If we've found the best iteration so far, update the UI
            $bestSoFar = $this->best();
            $this->status->setGeneration($generation)->setBest($bestSoFar);
            if ( null === $bestGenome || Marmoset\fitness($bestSoFar) < Marmoset\fitness($bestGenome)) {
                $bestGenome = $bestSoFar;

                if (0 === Marmoset\fitness($bestGenome)) {
                    $running = false;
                    $this->status->display();
                }
            }

        } while ($running);
    }
}