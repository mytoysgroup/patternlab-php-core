<?php
/**
 * Provides access to a file changes list.
 *
 */

namespace PatternLab;


use Twig_TokenStream;

class FileChangeList
{

  private static $changeHistoryFile;
  private static $fileChanges = [];
  private static $update = false;
  private function __construct() {
  }


  /**
   * write history
   *
   * @param String $changeHistoryFile csv file storing the filename and last change time
   */
  public static function init($changeHistoryFile) {
    self::$update = Config::getOption("update");

    if (self::$update) {
      self::$changeHistoryFile = $changeHistoryFile;
      if (!file_exists($changeHistoryFile)) {
        self::write();
      } else {
        self::read();
      }
    }
  }

  /**
   * write the history file by fileChanges array
   */
  public static function write() {
    if (self::$update) {
      $fp = fopen(self::$changeHistoryFile, 'w');
      foreach (self::$fileChanges as $fileChangesListKey => $fileChangesListData) {
        fputcsv($fp, [$fileChangesListKey, $fileChangesListData]);
      }
      fclose($fp);
    }
  }

  /**
   * Fill the fileChanges array by data from CSV
   *
   * @return array
   */
  private static function read() {

    // read file changes
    $fp = fopen(self::$changeHistoryFile, 'r');
    while(($row = fgetcsv($fp)) !== FALSE){
      self::$fileChanges[$row[0]] = $row[1];
    };
    fclose($fp);
    return self::$fileChanges;
  }

  /**
   * Check whether a file has changed by comparing the filemttime in CSV file and in file system
   *
   * @param $fileName
   * @return bool true if file has changed or does not exist and false if not
   */
  public static function hasChanged($fileName) {
      $hasChanged = true;
      if(self::$update) {
        $fileExists = false;
        if (file_exists($fileName)) {
          $fileExists = true;
        } else {
          $fileName = self::getConvertedFileName($fileName);
          if (file_exists($fileName)) {
            $fileExists = true;
          }
        }
        if ($fileExists) {
          $fileChangeTime = filemtime($fileName);

          if (array_key_exists($fileName, self::$fileChanges)) {
            if ($fileChangeTime == self::$fileChanges[$fileName]) {
              $hasChanged = false;
            }
          }

        }
      }
    return $hasChanged;
  }

  /**
   * Workaround since fileName is not stored with "-" instead of "~" TODO: remove
   *
   * @param $fileName
   * @return mixed
   */
  private static function getConvertedFileName($fileName) {
    // TODO: This is a workaround since in store the ~ is replaced by -
    $fileName = self::str_lreplace('-mytoys','~mytoys',$fileName);
    $fileName = self::str_lreplace('-myToys','~myToys',$fileName);
    return $fileName;
  }

  /**
   * Get file name by patterndata TODO: check if this can be moved outside this class
   *
   * @param $patternData
   * @return string
   */
  public static function getFileNameByPatternData($patternData) {
    $patternSourceDir = Config::getOption("patternSourceDir");
    $fileName = $patternSourceDir.DIRECTORY_SEPARATOR.$patternData['pathName'];
    if (!empty($patternData['ext'])){
      $fileName .= '.'.$patternData['ext'];
    }
    return $fileName;
  }

  /**
   * Add file name with filemtime to fileChanges array
   *
   * @param $fileName
   * @return bool
   */
  public static function update($fileName) {
    $update = false;
    if (self::$update) {
      if (file_exists($fileName)) {
        $update = true;
      } else {
        $fileName = self::getConvertedFileName($fileName);
        if (file_exists($fileName)) {
          $update = true;
        }
      }
      if ($update) {
        self::$fileChanges[$fileName] = filemtime($fileName);
      }
    }
    return $update;
  }

  /**
   * search for last occurence of a search string in subjcect and replaces it
   *
   * @param $search the search string that should be replaced
   * @param $replace the replace string
   * @param $subject the subject in which to search
   * @return mixed the replaced string
   */
  private static function str_lreplace($search, $replace, $subject)
  {
    $pos = strrpos($subject, $search);
    if($pos !== false) {
      $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
  }

  /**
   * Touch parent templates that include the updated pattern.
   *
   * @param $store
   */
  public static function touchParentPatterns($store) {
    if(self::$update) {
      foreach ($store as $patternStoreKey => $patternStoreData) {
        $patternSourceDir = Config::getOption("patternSourceDir");
        if ($patternStoreData["ext"] == "twig" && array_key_exists("patternRaw", $patternStoreData)) {
          $pr = $patternStoreData["patternRaw"];
          preg_match_all("/{%.*include.*twig/", $pr, $results);
          $includedFiles = array_unique(str_replace("{% include '", "", $results[0]));
          foreach ($includedFiles as $includedFile) {
            $includedFile = $patternSourceDir.DIRECTORY_SEPARATOR.$includedFile;
            if (file_exists($includedFile) && self::hasChanged($includedFile)) {
              $parentFileName = self::getFileNameByPatternData($patternStoreData);
              if (file_exists($parentFileName)) {
                touch($parentFileName, time());
                clearstatcache();
              }
            }
          }
          unset($includedFiles);
        }
      }
    }
  }

}