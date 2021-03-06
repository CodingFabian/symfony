<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\Console\Output;

require_once __DIR__.'/../../../bootstrap.php';

use Symfony\Components\Console\Output\ConsoleOutput;
use Symfony\Components\Console\Output\Output;

class ConsoleOutputTest extends \PHPUnit_Framework_TestCase
{
  public function testConstructor()
  {
    $output = new ConsoleOutput(Output::VERBOSITY_QUIET, true);
    $this->assertEquals($output->getVerbosity(), Output::VERBOSITY_QUIET, '__construct() takes the verbosity as its first argument');
  }
}
