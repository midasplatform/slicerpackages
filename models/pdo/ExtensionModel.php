<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
69328 Lyon, FRANCE.

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/
require_once BASE_PATH.'/modules/slicerpackages/models/base/ExtensionModelBase.php';

/**
 * Package PDO Model
 */
class Slicerpackages_ExtensionModel extends Slicerpackages_ExtensionModelBase
{
  static $excludeCategories;

  /**
   * Return all the records in the table
   * @param params Optional associative array specifying 'extension_id', 'os', 'arch',
   *               'submissiontype', 'packagetype', 'slicer_revision', 'revision',
   *               'productname', 'codebase', 'release' and 'category'.
   *               Can also specify 'order', 'direction', 'limit', and 'offset'.
   * @return array('extensions' => list of matching extensions,
   *               'total' => number of total matching extensions
   */
  function get($params = array('extension_id' => 'any', 'os' => 'any', 'arch' => 'any',
                               'submissiontype' => 'any', 'packagetype' => 'any',
                               'slicer_revision' => 'any', 'revision' => 'any',
                               'productname' => 'any', 'codebase' => 'any',
                               'release' => 'any', 'category' => 'any', 'search' => ''))
    {
    $sql = $this->database->select();
    $sqlCount = $this->database->select()
                     ->from(array($this->_name), array('count' => 'count(*)'));
    if(array_key_exists('search', $params) && $params['search'] != '')
      {
      $filterClause = "slicerpackages_extension.productname LIKE ?"
                  ." OR slicerpackages_extension.description LIKE ?";
      $pattern = '%'.$params['search'].'%';
      $sql->where($filterClause, $pattern);
      $sqlCount->where($filterClause, $pattern);
      }
    foreach(array('extension_id', 'os', 'arch', 'submissiontype', 'packagetype', 'revision', 'slicer_revision', 'productname', 'codebase', 'release', 'category') as $option)
      {
      if(array_key_exists($option, $params) && $params[$option] != 'any')
        {
        if($option == 'category') //category searches by prefix and among a list of categories
          {
          $category = $params['category'];
          $filterClause = "slicerpackages_extension.category = '".$category."'"
                      ." OR slicerpackages_extension.category LIKE '".$category.".%'"
                      ." OR slicerpackages_extension.category LIKE '".$category.";%'"
                      ." OR slicerpackages_extension.category LIKE '%;".$category.".%'"
                      ." OR slicerpackages_extension.category LIKE '%;".$category.";%'"
                      ." OR slicerpackages_extension.category LIKE '%;".$category."'";

          $sql->where($filterClause);
          $sqlCount->where($filterClause);
          }
        else
          {
          $fieldname = $option;
          if($option == 'extension_id')
            {
            $fieldname = 'slicerpackages_'.$option;
            }
          $filterClause = 'slicerpackages_extension.'.$fieldname.' = ?';
          $sql->where($filterClause, $params[$option]);
          $sqlCount->where($filterClause, $params[$option]);
          }
        }
      }
    if(!(array_key_exists('category', $params) && $params['category'] != 'any') &&
       !(array_key_exists('extension_id', $params) || array_key_exists('productname', $params)))
      {
      foreach(self::$excludeCategories as $exclude)
        {
        $filterClause = "NOT slicerpackages_extension.category LIKE ?";
        $sql->where($filterClause, $exclude);
        $sqlCount->where($filterClause, $exclude);
        }
      }
    if(array_key_exists('order', $params))
      {
      $direction = array_key_exists('direction', $params) ? strtoupper($params['direction']) : 'ASC';
      $sql->order($params['order'].' '.$direction);
      }
    if(array_key_exists('limit', $params) && is_numeric($params['limit']) && $params['limit'] > 0)
      {
      $offset = isset($params['offset']) ? $params['offset'] : 0;
      $sql->limit($params['limit'], $offset);
      }
    $rowset = $this->database->fetchAll($sql);
    $rowsetAnalysed = array();
    foreach($rowset as $keyRow => $row)
      {
      $tmpDao = $this->initDao('Extension', $row, 'slicerpackages');
      $rowsetAnalysed[] = $tmpDao;
      }
    $countRow = $this->database->fetchRow($sqlCount);
    return array('extensions' => $rowsetAnalysed, 'total' => $countRow['count']);
    }

  /** get all extension records */
  public function getAll()
    {
    return $this->database->getAll('Extension', 'slicerpackages');
    }

  /**
   * Return a slicerpackage_extension dao based on an itemId.
   */
  public function getByItemId($itemId)
    {
    $sql = $this->database->select()->where('item_id = ?', $itemId);
    $row = $this->database->fetchRow($sql);
    $dao = $this->initDao('Extension', $row, 'slicerpackages');
    return $dao;
    }

  /**
   * Return a category list for *all* categories in the database, and corresponding counts
   * filtered by a parameter array
   * @param params The filter array
   * @return An array whose keys are single categories and whose values are corresponding filtered counts
   */
  public function getCategoriesWithCounts($params)
    {
    // First build an array of all valid categories
    $allCategories = $this->getAllCategories();
    $categoryCounts = array();
    foreach($allCategories as $category)
      {
      $categoryCounts[$category] = 0;
      }

    // Now use group by query to get the filtered counts for each category
    $sql = $this->database->select()->setIntegrityCheck(false)
                ->from('slicerpackages_extension', array('category' => 'category', 'count' => 'count(*)'));
    foreach($params as $key => $value)
      {
      if($key == 'search')
        {
        $filterClause = "productname LIKE ? OR description LIKE ?";
        $sql->where($filterClause, '%'.$value.'%');
        }
      else
        {
        $sql->where($key.' = ?', $value);
        }
      }
    $sql->where('category != ?', '');
    $sql->group('category');

    $rows = $this->database->fetchAll($sql);
    foreach($rows as $row)
      {
      $categoryList = explode(';', $row['category']);
      foreach($categoryList as $category)
        {
        $categoryCounts[$category] += $row['count'];
        }
      }
    return $categoryCounts;
    }


  /**
   * Return a list of all distinct categories of all the extensions
   * in the database
   */
  public function getAllCategories()
    {
    $sql = $this->database->select()
                ->from(array('e' => 'slicerpackages_extension'), array('category'))
                ->where('category != ?', '')
                ->distinct();
    $categories = array();
    $rowset = $this->database->fetchAll($sql);
    foreach($rowset as $row)
      {
      $categoryList = explode(';', $row['category']);
      foreach($categoryList as $category)
        {
        $categories[$category] = 1;
        }
      }
    return array_keys($categories);
    }

  /**
   * Return a list of all distinct releases
   */
  public function getAllReleases()
    {
    $sql = $this->database->select()
                ->from(array('e' => 'slicerpackages_extension'), array('release'))
                ->where('e.release != ?', '')
                ->distinct();
    $releases = array();
    $rowset = $this->database->fetchAll($sql);
    foreach($rowset as $row)
      {
      $releases[] = $row['release'];
      }
    return $releases;
    }
}  // end class

Slicerpackages_ExtensionModel::$excludeCategories = array('examples');
