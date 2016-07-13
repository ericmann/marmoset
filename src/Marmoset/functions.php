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