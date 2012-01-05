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
require_once BASE_PATH.'/modules/slicerpackages/models/base/PackageModelBase.php';

/**
 * Package PDO Model
 */
class Slicerpackages_PackageModel extends Slicerpackages_PackageModelBase
{
  /**
   * Return all the record in the table
   * @param params Optional associative array specifying an 'os', 'arch', 'submissiontype' and 'packagetype'.
   * @return Array of SlicerpackagesDao
   */
  function get($params = array('os' => 'any', 'arch' => 'any',
                               'submissiontype' => 'any', 'packagetype' => 'any',
                               'revision' => 'any', 'productname' => 'any',
                               'codebase' => 'any'))
    {
    $sql = $this->database->select();
    foreach(array('os', 'arch', 'submissiontype', 'packagetype', 'revision', 'productname', 'codebase') as $option)
      {
      if(array_key_exists($option, $params) && $params[$option] != 'any')
        {
        $sql->where($option.' = ?', $params[$option]);
        }
      }
    if(array_key_exists('order', $params))
      {
      $direction = array_key_exists('direction', $params) ? strtoupper($params['direction']) : 'ASC';
      $sql->order($params['order'].' '.$direction);
      }
    if(array_key_exists('limit', $params) && is_numeric($params['limit']) && $params['limit'] > 0)
      {
      $sql->limit($params['limit']);
      }
    $rowset = $this->database->fetchAll($sql);
    $rowsetAnalysed = array();
    foreach($rowset as $keyRow => $row)
      {
      $tmpDao = $this->initDao('Package', $row, 'slicerpackages');
      $rowsetAnalysed[] = $tmpDao;
      }
    return $rowsetAnalysed;
    }

  /** get all package records */
  public function getAll()
    {
    return $this->database->getAll('Package', 'slicerpackages');
    }

  /**
   * Return a slicerpackage_Package dao based on an itemId.
   */
  public function getByItemId($itemId)
    {
    $sql = $this->database->select()->where('item_id = ?', $itemId);
    $row = $this->database->fetchRow($sql);
    $dao = $this->initDao('Package', $row, 'slicerpackages');
    return $dao;
    }

  function getReleasedPackages($folderDaos, $releases = array())
    {
    if(!is_array($folderDaos))
      {
      $folderDaos = array($folderDaos);
      }
    if(!is_array($releases))
      {
      $releases = array($releases);
      }
    $select = $this->database->select()
                   ->setIntegrityCheck(false)
                   ->from(array('i' => 'item'), array())
                   ->join(array('i2f' => 'item2folder'), 'i2f.item_id = i.item_id', array())
                   ->join(array('f' => 'folder'), 'f.folder_id = i2f.folder_id', array())
                   ->join(array('sp' => 'slicerpackages_package'), 'sp.item_id = i.item_id');

    if (count($releases) > 0)
      {
      $select->where('sp.release IN (?)', $releases);
      }
    else
      {
      $select->where('sp.release != ""');
      }
    $rowset = $this->database->fetchAll($select);
    $packageDaos = array();
    foreach($rowset as $row)
      {
      $packageDaos[] = $this->initDao('Slicerpackages_Package', $row);
      }
    return $packageDaos;
    }
}  // end class
