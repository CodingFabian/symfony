<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\OutputEscaper;

require_once __DIR__.'/../../bootstrap.php';

use Symfony\Components\Yaml\Yaml;
use Symfony\Components\Yaml\Parser;
use Symfony\Components\Yaml\Dumper;

class DumperTest extends \PHPUnit_Framework_TestCase
{
  protected $parser;
  protected $dumper;
  protected $path;

  static public function setUpBeforeClass()
  {
    Yaml::setSpecVersion('1.1');
  }

  public function setUp()
  {
    $this->parser = new Parser();
    $this->dumper = new Dumper();
    $this->path = __DIR__.'/../../../../fixtures/Symfony/Components/Yaml';
  }

  public function testSpecifications()
  {
    $files = $this->parser->parse(file_get_contents($this->path.'/index.yml'));
    foreach ($files as $file)
    {
      $yamls = file_get_contents($this->path.'/'.$file.'.yml');

      // split YAMLs documents
      foreach (preg_split('/^---( %YAML\:1\.0)?/m', $yamls) as $yaml)
      {
        if (!$yaml)
        {
          continue;
        }

        $test = $this->parser->parse($yaml);
        if (isset($test['dump_skip']) && $test['dump_skip'])
        {
          continue;
        }
        else if (isset($test['todo']) && $test['todo'])
        {
          // TODO
        }
        else
        {
          $expected = eval('return '.trim($test['php']).';');

          $this->assertEquals($this->parser->parse($this->dumper->dump($expected, 10)), $expected, $test['test']);
        }
      }
    }
  }

  public function testInlineLevel()
  {
    // inline level
    $array = array(
      '' => 'bar',
      'foo' => '#bar',
      'foo\'bar' => array(),
      'bar' => array(1, 'foo'),
      'foobar' => array(
        'foo' => 'bar',
        'bar' => array(1, 'foo'),
        'foobar' => array(
          'foo' => 'bar',
          'bar' => array(1, 'foo'),
        ),
      ),
    );

    $expected = <<<EOF
{ '': bar, foo: '#bar', 'foo''bar': {  }, bar: [1, foo], foobar: { foo: bar, bar: [1, foo], foobar: { foo: bar, bar: [1, foo] } } }
EOF;
$this->assertEquals($this->dumper->dump($array, -10), $expected, '->dump() takes an inline level argument');
$this->assertEquals($this->dumper->dump($array, 0), $expected, '->dump() takes an inline level argument');

$expected = <<<EOF
'': bar
foo: '#bar'
'foo''bar': {  }
bar: [1, foo]
foobar: { foo: bar, bar: [1, foo], foobar: { foo: bar, bar: [1, foo] } }

EOF;
    $this->assertEquals($this->dumper->dump($array, 1), $expected, '->dump() takes an inline level argument');

    $expected = <<<EOF
'': bar
foo: '#bar'
'foo''bar': {  }
bar:
  - 1
  - foo
foobar:
  foo: bar
  bar: [1, foo]
  foobar: { foo: bar, bar: [1, foo] }

EOF;
    $this->assertEquals($this->dumper->dump($array, 2), $expected, '->dump() takes an inline level argument');

    $expected = <<<EOF
'': bar
foo: '#bar'
'foo''bar': {  }
bar:
  - 1
  - foo
foobar:
  foo: bar
  bar:
    - 1
    - foo
  foobar:
    foo: bar
    bar: [1, foo]

EOF;
    $this->assertEquals($this->dumper->dump($array, 3), $expected, '->dump() takes an inline level argument');

    $expected = <<<EOF
'': bar
foo: '#bar'
'foo''bar': {  }
bar:
  - 1
  - foo
foobar:
  foo: bar
  bar:
    - 1
    - foo
  foobar:
    foo: bar
    bar:
      - 1
      - foo

EOF;
    $this->assertEquals($this->dumper->dump($array, 4), $expected, '->dump() takes an inline level argument');
    $this->assertEquals($this->dumper->dump($array, 10), $expected, '->dump() takes an inline level argument');
  }

  public function testObjectsSupport()
  {
    $a = array('foo' => new A(), 'bar' => 1);

    $this->assertEquals($this->dumper->dump($a), '{ foo: !!php/object:O:40:"Symfony\Tests\Components\OutputEscaper\A":1:{s:1:"a";s:3:"foo";}, bar: 1 }', '->dump() is able to dump objects');
  }
}

class A
{
  public $a = 'foo';
}
