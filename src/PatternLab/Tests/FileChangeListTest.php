<?php
/**
 * Created by PhpStorm.
 * User: mzseby
 * Date: 07.01.16
 * Time: 09:50
 */

namespace PatternLab\Tests;


use PatternLab\Config;
use PatternLab\FileChangeList;

class FileChangeListTest extends \PHPUnit_Framework_TestCase
{

  public function testIsUpdateWithReplace() {
    Config::init(__DIR__."/../../../../../../", false);
    $sd = Config::getOption("patternSourceDir");
    $fileName = $sd . "/04-pages/pdp/pdp~mytoys-desktop.json";
    $patternStore = ['bla' => ['pathName' => "04-pages/pdp/pdp-mytoys-desktop", 'ext' => 'json']];

    FileChangeList::init(Config::getOption("publicDir").DIRECTORY_SEPARATOR."fileChangeList.csv");
    $this->assertTrue(touch($fileName, time() + 100));
    FileChangeList::write();
    touch($fileName, time());
    clearstatcache();
    FileChangeList::init(Config::getOption("publicDir").DIRECTORY_SEPARATOR."fileChangeList.csv");

    $condition = FileChangeList::hasChanged($sd . "/04-pages/pdp/pdp-mytoys-desktop.json");
    $this->assertTrue($condition);
  }

  public function testIsUpdate() {
    Config::init(__DIR__."/../../../../../../", false);
    $sd = Config::getOption("patternSourceDir");
    $fileName = $sd . "/00-atoms/_unused/banner.twig";
    $patternStore = ['bla' => ['pathName' => "00-atoms/_unused/banner", 'ext' => 'twig']];

    FileChangeList::init(Config::getOption("publicDir").DIRECTORY_SEPARATOR."fileChangeList.csv");
    $this->assertTrue(touch($fileName, time() + 100));
    FileChangeList::write();
    touch($fileName, time());
    clearstatcache();
    FileChangeList::init(Config::getOption("publicDir").DIRECTORY_SEPARATOR."fileChangeList.csv");

    $condition = FileChangeList::hasChanged($fileName);
    $this->assertTrue($condition);
  }
}