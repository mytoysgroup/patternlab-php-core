<?php

namespace PatternLab\Tests;

use PatternLab\Config;
use PatternLab\Console;
use PatternLab\Dispatcher;
use PatternLab\Generator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

  public function testGenerate() {
    Console::init();
    Config::init( __DIR__."/../../../../../..");
    Dispatcher::init();
    $generator = new Generator();
    $generator->generate(['foo' => 'baz']);
echo '';
  }
}
