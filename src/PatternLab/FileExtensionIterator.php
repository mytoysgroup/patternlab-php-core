<?php
/**
 * Created by PhpStorm.
 * User: mzseby
 * Date: 07.01.16
 * Time: 10:38
 */

namespace PatternLab;


class FileExtensionIterator extends \RegexIterator
{


  public function __construct($path, $extension) {
    $dir = new \RecursiveDirectoryIterator($path);
    $iterator = new \RecursiveIteratorIterator($dir);
    parent::__construct($iterator, '/^.+\.' . $extension . '$/i', \RecursiveRegexIterator::GET_MATCH);
  }
}