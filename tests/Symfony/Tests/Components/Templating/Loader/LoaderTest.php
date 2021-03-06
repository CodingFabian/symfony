<?php

/*
 * This file is part of the symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\Templating\Loader;

require_once __DIR__.'/../../../bootstrap.php';

require_once __DIR__.'/../../../../../lib/SymfonyTests/Components/Templating/ProjectTemplateDebugger.php';

use Symfony\Components\Templating\Loader\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
  public function testGetSetDebugger()
  {
    $loader = new ProjectTemplateLoader4();
    $loader->setDebugger($debugger = new \ProjectTemplateDebugger());
    $this->assertTrue($loader->getDebugger() === $debugger, '->setDebugger() sets the debugger instance');
  }
}

class ProjectTemplateLoader4 extends Loader
{
  public function load($template, array $options = array())
  {
  }

  public function getDebugger()
  {
    return $this->debugger;
  }
}
