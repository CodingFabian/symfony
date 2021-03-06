<?php

/*
 * This file is part of the symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\Templating\Storage;

require_once __DIR__.'/../../../bootstrap.php';

use Symfony\Components\Templating\Storage\Storage;
use Symfony\Components\Templating\Renderer\PhpRenderer;

class StorageTest extends \PHPUnit_Framework_TestCase
{
  public function testMagicToString()
  {
    $storage = new TestStorage('foo');
    $this->assertEquals((string) $storage, 'foo', '__toString() returns the template name');
  }

  public function testGetRenderer()
  {
    $storage = new TestStorage('foo', $renderer = new PhpRenderer());
    $this->assertTrue($storage->getRenderer() === $renderer, '->getRenderer() returns the renderer');
  }
}

class TestStorage extends Storage
{
  public function getContent()
  {
  }
}
