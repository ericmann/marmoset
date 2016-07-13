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
    const MUTATION_PROBABILITY = 0.50;

    const CROSSOVER_PROBABILITY = 0.85;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var array
     */
    protected $population;

    /*protected $target = <<<PHP
To be or not to be, that is the question;
Whether 'tis nobler in the mind to suffer
The slings and arrows of outrageous fortune,
Or to take arms against a sea of troubles,
And by opposing, end them.
PHP;*/

    protected $target = <<<PHP
I love PHP!
PHP;

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
     * Create a random population with a specific number of members
     *
     * @param int $count
     * @param int $length
     *
     * @return \Generator
     */
    protected function random_population(int $count, int $length)
    {
        while ($count--) {
            yield implode('', iterator_to_array($this->random_genome($length)));
        }
    }

    /**
     * Create a random genome given a character length.
     *
     * @param int $length
     *
     * @return \Generator
     */
    protected function random_genome(int $length)
    {
        while ($length--) {
            yield $this->validChars()[ mt_rand(0, 99) ];
        }
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
            return max($max, $this->fitness($current));
        }, 0) + 1.0;

        // Sum of max minus fitness
        $sumOfMaxMinusFitness = array_reduce($this->population, function(float $carry, string $current) use ($maxFitness) {
            return $carry + ($maxFitness - $this->fitness($current));
        }, 0);

        $newPop = [];
        if (false) {
            // foreach parallel range 1=>pop/2
            //   findrandomparent
            //   findrandomparent
            //   create two children
        } else {
            foreach (range(1, count($this->population) / 2) as $counter) {
                $parent1 = $this->random_high_quality_parent($sumOfMaxMinusFitness, $maxFitness);
                $parent2 = $this->random_high_quality_parent($sumOfMaxMinusFitness, $maxFitness);
                $newPop = array_merge($newPop, $this->create_children($parent1, $parent2));
            }
        }

        return $newPop;
    }

    /**
     * Create two child nodes given two parent nodes, with an inherent probability
     * that the strings from the parents will crossover and/or mutate while creating
     * the child nodes.
     *
     * @param $parent1
     * @param $parent2
     *
     * @return array
     */
    protected function create_children(string $parent1, string $parent2)
    {
        // Crossover
        if ($this->random_float() < self::CROSSOVER_PROBABILITY) {
            list($child1, $child2) = $this->crossover($parent1, $parent2);
        } else {
            $child1 = $parent1;
            $child2 = $parent2;
        }

        // Mutate
        if ($this->random_float() < self::MUTATION_PROBABILITY) $child1 = $this->mutate($child1);
        if ($this->random_float() < self::MUTATION_PROBABILITY) $child2 = $this->mutate($child2);

        // Return children
        return [$child1, $child2];
    }

    /**
     * Generate a new tuple of children after exchanging part of the originals.
     *
     * @param string $first
     * @param string $second
     *
     * @return array
     */
    protected function crossover(string $first, string $second)
    {
        $crossover_point = mt_rand(0, strlen($first) - 1);

        $new_first = substr($first, 0, $crossover_point) . substr($second, $crossover_point);
        $new_second = substr($second, 0, $crossover_point) . substr($first, $crossover_point);

        return [$new_first, $new_second];
    }

    /**
     * Return a mutated alternative to the specified genome.
     *
     * @param string $genome
     *
     * @return string
     */
    protected function mutate(string $genome)
    {
        $new_genome = $genome;
        $upDown = mt_rand( 0, 10 ) < 5 ? -1 : 1;
        $index = mt_rand( 0, strlen( $new_genome ) - 1 );
        $new_genome[ $index ] = chr( ord( $genome[ $index ] ) + $upDown );

        return $new_genome;
    }

    /**
     * @param float $sum Sum of (max fitness - fitness) for all potential parents
     * @param float $max Max fitness across all potential parents
     *
     * @return string
     */
    protected function random_high_quality_parent(float $sum, float $max)
    {
        $val = $this->random_float() * $sum;

        for ($i = 0; $i < count($this->population); $i++) {
            $maxMinusFitness = $max - $this->fitness($this->population[ $i ]);
            if ($val < $maxMinusFitness) {
                return $this->population[ $i ];
            }
            $val -= $maxMinusFitness;
        }
    }

    /**
     * Return a random floating-point number (for maths)
     *
     * @return float
     */
    protected function random_float()
    {
        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
    }

    /**
     * Calculate the Euclidean distance of a test string vector from the previously-specified target vector.
     *
     * @param string $test
     *
     * @return float
     */
    protected function fitness(string $test)
    {
        return array_reduce(range(0, strlen($this->target) - 1), function ($out, $i) use ($test) {
            $out += pow(ord($test[ $i ]) - ord($this->target[ $i ]), 2);

            return $out;
        }, 0);
    }

    /**
     * Build up the array of allowed characters in our system.
     *
     * @return array
     */
    protected function validChars()
    {
        static $_validChars;

        if (!$_validChars) {
            $_validChars[] = chr(10);
            $_validChars[] = chr(13);
            for ($i = 2, $pos = 32; $i < 97; $i++, $pos++) {
                $_validChars[] = chr($pos);
            }
        }

        return $_validChars;
    }

    /**
     * Get the best member of the generation
     *
     * @return string
     */
    protected function best()
    {
        return array_reduce($this->population, function (string $best, string $current) {
            if ($this->fitness($current) < $this->fitness($best)) {
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

        $this->population = iterator_to_array($this->random_population(200, strlen($this->target)));

        $running = true;
        do {
            // Move to the next generation
            $generation += 1;
            $this->population = $this->create_next_generation();

            // If we've found the best iteration so far, update the UI
            $bestSoFar = $this->best();
            $this->status->setGeneration($generation)->setBest($bestSoFar);
            if ( null === $bestGenome || $this->fitness($bestSoFar) < $this->fitness($bestGenome)) {
                $bestGenome = $bestSoFar;

                if (0 === $this->fitness($bestGenome)) {
                    $running = false;
                    $this->status->display();
                }
            }

        } while ($running);
    }
}