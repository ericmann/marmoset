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

const MUTATION_PROBABILITY = 0.50;

const CROSSOVER_PROBABILITY = 0.85;

const TTARGET = <<<PHP
To be or not to be, that is the question;
Whether 'tis nobler in the mind to suffer
The slings and arrows of outrageous fortune,
Or to take arms against a sea of troubles,
And by opposing, end them.
PHP;

const TARGET = <<<PHP
I love PHP!
PHP;

/**
 * Build up the array of allowed characters in our system.
 *
 * @return array
 */
function validChars()
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
 * Create a random genome given a character length.
 *
 * @param int $length
 *
 * @return \Generator
 */
function random_genome(int $length)
{
    while ($length--) {
        yield validChars()[ mt_rand(0, 99) ];
    }
}

/**
 * Create a random population with a specific number of members
 *
 * @param int $count
 * @param int $length
 *
 * @return \Generator
 */
function random_population(int $count, int $length)
{
    while ($count--) {
        yield implode('', iterator_to_array(random_genome($length)));
    }
}

/**
 * Return a random floating-point number (for maths)
 *
 * @return float
 */
function random_float()
{
    return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
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
function create_children(string $parent1, string $parent2)
{
    // Crossover
    if (random_float() < CROSSOVER_PROBABILITY) {
        list($child1, $child2) = crossover($parent1, $parent2);
    } else {
        $child1 = $parent1;
        $child2 = $parent2;
    }

    // Mutate
    if (random_float() < MUTATION_PROBABILITY) $child1 = mutate($child1);
    if (random_float() < MUTATION_PROBABILITY) $child2 = mutate($child2);

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
function crossover(string $first, string $second)
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
function mutate(string $genome)
{
    $new_genome = $genome;
    $upDown = mt_rand( 0, 10 ) < 5 ? -1 : 1;
    $index = mt_rand( 0, strlen( $new_genome ) - 1 );
    $new_genome[ $index ] = chr( ord( $genome[ $index ] ) + $upDown );

    return $new_genome;
}

/**
 * Calculate the Euclidean distance of a test string vector from the previously-specified target vector.
 *
 * @param string $test
 *
 * @return float
 */
function fitness(string $test)
{
    return array_reduce(range(0, strlen(TARGET) - 1), function ($out, $i) use ($test) {
        $out += pow(ord($test[ $i ]) - ord(TARGET[ $i ]), 2);

        return $out;
    }, 0);
}