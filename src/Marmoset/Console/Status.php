<?php
/*
 * This file is part of the Marmoset project.
 *
 * (c) Eric Mann <eric@eamann.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EAMann\Marmoset\Console;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Status provides helpers to display progress output.
 *
 * Heavily derived from Symfony Console's ProgressBar helper.
 *
 * @author Eric Mann <eric@eamann.com>
 */
class Status
{

	private $redrawFreq = 1;
	private $last;

	/**
	 * @var OutputInterface
	 */
	private $output;
	private $startTime;
	private $generation = 0;
	private $generations_per_second = 0;
	private $best = '';
	private $formatLineCount;
	private $overwrite = true;

	/**
	 * Constructor.
	 *
	 * @param OutputInterface $output An OutputInterface instance
	 * @param int             $max    Maximum steps (0 if unknown)
	 */
	public function __construct(OutputInterface $output, $max = 0)
	{
		if ($output instanceof ConsoleOutputInterface) {
			$output = $output->getErrorOutput();
		}

		$this->output = $output;

		if (!$this->output->isDecorated()) {
			// disable overwrite when output does not support ANSI codes.
			$this->overwrite = false;
			// set a reasonable redraw frequency so output isn't flooded
			$this->setRedrawFrequency($max / 10);
		}

		$this->last = $this->startTime = time();
		$this->generation = 0;
		$this->generations_per_second = 0;
		$this->best = '';
	}

	/**
	 * Gets the output start time.
	 *
	 * @return int The output start time
	 */
	public function getStartTime()
	{
		return $this->startTime;
	}

	/**
	 * Sets the redraw frequency.
	 *
	 * @param int|float $freq The frequency in steps
	 */
	public function setRedrawFrequency($freq)
	{
		$this->redrawFreq = max((int) $freq, 1);
	}

	/**
	 * Starts the output.
	 *
	 * @param string [$best] Optional pre-population of the best attempt
	 */
	public function start( $best = '' )
	{
		$this->last = $this->startTime = time();
		$this->generation = 0;
		$this->generations_per_second = 0;
		$this->best = $best;

		$this->display();
	}

	/**
	 * Sets whether to overwrite the status, false for new line.
	 *
	 * @param bool $overwrite
	 */
	public function setOverwrite( $overwrite )
	{
		$this->overwrite = (bool) $overwrite;
	}

	/**
	 * Update the current generation count and generation rate.
	 *
	 * @param int $generation
	 */
	public function setGeneration( $generation )
	{
		$now = time();
		$this->generation = (int) $generation;

		if ( $now > $this->startTime ) {
			$this->generations_per_second = floor( $generation / ( $now - $this->startTime ) );
		}

		if ( $now > ( $this->last + $this->redrawFreq ) ) {
			$this->display();
		}
	}

	/**
	 * Set the monkeys' best guess at writing Shakespeare.
	 *
	 * @param string $best
	 */
	public function setBest( $best )
	{
		$this->best = $best;

		if ( time() > ( $this->last + $this->redrawFreq ) ) {
			$this->display();
		}
	}

	/**
	 * Outputs the current status string.
	 */
	public function display()
	{
		static $header = false;

		$this->last = time();
		$status = '';

		if ( ! $header ) {
			$status .= <<<PHP
  __  __             _                    _         _   _            __  __            _     _            
 |  \/  |           | |                  (_)       | | | |          |  \/  |          | |   (_)           
 | \  / | ___  _ __ | | _____ _   _ ___   _ _ __   | |_| |__   ___  | \  / | __ _  ___| |__  _ _ __   ___ 
 | |\/| |/ _ \| '_ \| |/ / _ \ | | / __| | | '_ \  | __| '_ \ / _ \ | |\/| |/ _` |/ __| '_ \| | '_ \ / _ \
 | |  | | (_) | | | |   <  __/ |_| \__ \ | | | | | | |_| | | |  __/ | |  | | (_| | (__| | | | | | | |  __/
 |_|  |_|\___/|_| |_|_|\_\___|\__, |___/ |_|_| |_|  \__|_| |_|\___| |_|  |_|\__,_|\___|_| |_|_|_| |_|\___|
                               __/ |                                                                      
                              |___/                                                                       			
			
                                                  `.::-.            
                                             `:+syyssyyys+.        
                                            :ysssssssssyyyho`      
                                      `/+/-:ys+:::/oo+//+syys`     
                                     -yysoshs:..-:-----:-.:/yo+oo/`
                                     sys--:s  `- .:---/ `:  .y++yyo
                                     :yyo/so.``-`-/++++.`.``:o--oys
                                       -:oyo/:::::-:/::::::/yhsshy:
                                         sy::::-----/--:::-:+ys.`  
                                         /h:::::----/--:::::+h/    
                                          -+/-------:-----:/y/     
                                            --``.-:::-::///:`      
                                           `os:-oosyyyyys/   :oso/ 
                                            +++oo/:--/+syyo -yh+yy:
                                            s/:://---:+oyyh`+ys`/: 
                                            `yooss/-:yyyyys/yy.    
                                             syys+////syhhy+:`     
                                           -+syyyyyosyyyyyo:`      
                                           :+oosso: `ossssss:      
                                             
|--------------------------------------------------------------------------------------------------------|
|   Current                                                                   | Generation |  Gen / sec  |
|-----------------------------------------------------------------------------|------------|-------------|\n
PHP;
			$header = true;
		}

		$gen = (string) $this->generation;
		$gps = (string) $this->generations_per_second;

		// Build the status string
		$status .= "|" . str_repeat( ' ', 77 ) . "|" . str_repeat( ' ', 11 - strlen( $gen ) ) .$gen . " |" . str_repeat( ' ', 12 - strlen( $gps ) ) . $gps . " |\n";

		// Make sure we can overwrite previous lines
		$this->formatLineCount = 1;

		// Overwrite all the things
		$this->overwrite( $status );
	}

	/**
	 * Removes the status from the current line.
	 *
	 * Call display() to show the status again.
	 */
	public function clear()
	{
		if (!$this->overwrite) {
			return;
		}

		$this->overwrite('');
	}

	/**
	 * Overwrites a previous message to the output.
	 *
	 * @param string $message The message
	 */
	private function overwrite( $message )
	{
		if ( $this->overwrite ) {
			// Move the cursor to the beginning of the line
			$this->output->write( "\x0D" );

			// Erase the line
			$this->output->write( "\x1B[2K" );

			// Erase previous lines
			if ( $this->formatLineCount > 0 ) {
				$this->output->write( str_repeat( "\x1B[1A\x1B[2K", $this->formatLineCount ) );
			}
		} else {
			$this->output->writeln( '' );
		}

		$this->output->write($message);
	}
}