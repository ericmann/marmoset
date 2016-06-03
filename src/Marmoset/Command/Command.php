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
	protected function configure()
	{
		$this->setName( 'run' )
			->setDescription( 'Make the monkeys type' )
			->addOption(
				'mode',
				null,
				InputOption::VALUE_OPTIONAL,
				'Run-mode',
				'synchronous'
			);
	}

	/**
	 * Actually execute the command
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return null|int null or 0 if everything went fine, or an error code
	 */
	public function execute( InputInterface $input, OutputInterface $output )
	{
		$status = new Status( $output );

		$gen = 0;

		do {
			$gen += 1;
			$status->setGeneration( $gen );
		} while( true );
	}
}