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

        if (getenv('ASYNC')) {
            $pool = new Marmoset\Troop(2, Marmoset\Worker::class);

            foreach (range(1, count($this->population) / 2) as $counter) {
                $pool->submit(new Marmoset\Job($this->population, $sumOfMaxMinusFitness, $maxFitness));
            }

            $children = $pool->process();

            while( count($children) < count($this->population)) {
                $children[] = $this->population[rand(0, count($this->population) - 1)];
            }

            return (array) $children;
        } else {
            $newPop = [];

            foreach (range(1, count($this->population) / 2) as $counter) {
                $parent1 = Marmoset\random_high_quality_parent($this->population, $sumOfMaxMinusFitness, $maxFitness);
                $parent2 = Marmoset\random_high_quality_parent($this->population, $sumOfMaxMinusFitness, $maxFitness);
                $newPop = array_merge($newPop, Marmoset\create_children($parent1, $parent2));
            }

            return $newPop;
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