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
  /**
   * Return all the records in the table
   * @param params Optional associative array specifying an 'os', 'arch', 'submissiontype' and 'packagetype'.
                   Can also specify 'order', 'direction', 'limit', and 'offset'.
   * @return Array of Slicer extension Daos
   */
  function get($params = array('os' => 'any', 'arch' => 'any',
                               'submissiontype' => 'any', 'packagetype' => 'any',
                               'slicer_revision' => 'any', 'revision' => 'any',
                               'productname' => 'any', 'codebase' => 'any',
                               'release' => 'any', 'category' => 'any'))
    {
    $sql = $this->database->select();
    foreach(array('os', 'arch', 'submissiontype', 'packagetype', 'revision', 'slicer_revision', 'productname', 'codebase', 'release', 'category') as $option)
      {
      if(array_key_exists($option, $params) && $params[$option] != 'any')
        {
        $sql->where('slicerpackages_extension.'.$option.' = ?', $params[$option]);
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
    return $rowsetAnalysed;
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

}  // end class
