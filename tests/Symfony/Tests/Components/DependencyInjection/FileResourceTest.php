<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\DependencyInjection;

require_once __DIR__.'/../../bootstrap.php';

use Symfony\Components\DependencyInjection\FileResource;

class FileResourceTest extends \PHPUnit_Framework_TestCase
{
  protected $resource;
  protected $file;

  public function setUp()
  {
    $this->file = sys_get_temp_dir().'/tmp.xml';
    touch($this->file);
    $this->resource = new FileResource($this->file);
  }

  public function tearDown()
  {
    unlink($this->file);
  }

  public function testGetResource()
  {
    $this->assertEquals($this->resource->getResource(), $this->file, '->getResource() returns the path to the resource');
  }

  public function testIsUptodate()
  {
    $this->assertTrue($this->resource->isUptodate(time() + 10), '->isUptodate() returns true if the resource has not changed');
    $this->assertTrue(!$this->resource->isUptodate(time() - 86400), '->isUptodate() returns false if the resource has been updated');

    $resource = new FileResource('/____foo/foobar'.rand(1, 999999));
    $this->assertTrue(!$resource->isUptodate(time()), '->isUptodate() returns false if the resource does not exist');
  }
}
