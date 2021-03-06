<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\DependencyInjection\Dumper;

require_once __DIR__.'/../../../bootstrap.php';

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\Dumper\XmlDumper;

class XmlDumperTest extends \PHPUnit_Framework_TestCase
{
  static protected $fixturesPath;

  static public function setUpBeforeClass()
  {
    self::$fixturesPath = realpath(__DIR__.'/../../../../../fixtures/Symfony/Components/DependencyInjection/');
  }

  public function testDump()
  {
    $dumper = new XmlDumper($container = new Builder());

    $this->assertEquals($dumper->dump(), file_get_contents(self::$fixturesPath.'/xml/services1.xml'), '->dump() dumps an empty container as an empty XML file');

    $container = new Builder();
    $dumper = new XmlDumper($container);
  }

  public function testAddParemeters()
  {
    $container = include self::$fixturesPath.'//containers/container8.php';
    $dumper = new XmlDumper($container);
    $this->assertEquals($dumper->dump(), file_get_contents(self::$fixturesPath.'/xml/services8.xml'), '->dump() dumps parameters');
  }

  public function testAddService()
  {
    $container = include self::$fixturesPath.'/containers/container9.php';
    $dumper = new XmlDumper($container);
    $this->assertEquals($dumper->dump(), str_replace('%path%', self::$fixturesPath.'/includes', file_get_contents(self::$fixturesPath.'/xml/services9.xml')), '->dump() dumps services');

    $dumper = new XmlDumper($container = new Builder());
    $container->register('foo', 'FooClass')->addArgument(new \stdClass());
    try
    {
      $dumper->dump();
      $this->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
    }
    catch (\RuntimeException $e)
    {
    }
  }
}
