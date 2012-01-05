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

/**
 * Slicepackages Model Base
 */
abstract class Slicerpackages_PackageModelBase extends Slicerpackages_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'slicerpackages_package';
    $this->_key = 'package_id';
    $this->_mainData = array(
        'package_id' =>  array('type' => MIDAS_DATA),
        'item_id' => array('type' => MIDAS_DATA),
        'os' => array('type' => MIDAS_DATA),
        'arch' => array('type' => MIDAS_DATA),
        'revision' => array('type' => MIDAS_DATA),
        'submissiontype' => array('type' => MIDAS_DATA),
        'packagetype' => array('type' => MIDAS_DATA),
        'productname' => array('type' => MIDAS_DATA),
        'codebase' => array('type' => MIDAS_DATA),
        'checkoutdate' => array('type' => MIDAS_DATA),
        'release' => array('type' => MIDAS_DATA),
        'item' =>  array('type' => MIDAS_MANY_TO_ONE,
                         'model' => 'Item',
                         'parent_column' => 'item_id',
                         'child_column' => 'item_id'),
      );
    $this->initialize(); // required
    } // end __construct()

  public abstract function getAll();
  public abstract function getByItemId($itemId);

  /** Get the last created packages belonging to the provided FolderDaos and matching operatingSystems.
   * @param Array $folderDaos List of FolderDaos containing the packages to consider.
   * @return Array Each element is an associative array having the keys 'package_id',
   * 'item_id' and 'date_creation'
   *
   * Note that this method does NOT check if the current user has access to the packages associated
   * with the provided folders.
   */
  abstract function getMostRecentCreatedItem($folderDaos);

  /** Get the last created packages belonging to the provided FolderDaos and matching operatingSystems.
   * @param Array $folderDaos List of FolderDaos containing the packages to consider.
   * @param string|array $operatingSystems If no operating system is specified, all operating
   * systems associated with the provided folderDaos will be considered.
   * @return Array Each element is an associative array having the keys 'package_id',
   * 'item_id', 'os' and 'date_creation'
   *
   * Note that this method does NOT check if the current user has access to the packages associated
   * with the provided folders.
   */
  abstract function getMostRecentCreatedItemsByOs($folderDaos, $operatingSystems = array());

  /** Get the last created packages belonging to the provided FolderDaos and matching both operatingSystems and architectures.
   * @param Array $folderDaos List of FolderDaos containing the packages to consider.
   * @param string|array $operatingSystems If no operating system is specified, all operating
   * systems associated with the provided folderDaos will be considered.
   * @param string|array $architectures If no architecture is specified, all architectures
   * associated with the provided folderDaos will be considered.
   * @return Array Each element is an associative array having the keys 'package_id',
   * 'item_id', 'os', 'arch' and 'date_creation'
   *
   * Note that this method does NOT check if the current user has access to the packages associated
   * with the provided folders.
   */
  abstract function getMostRecentCreatedItemsByOsAndArch($folderDaos, $operatingSystems = array(), $architectures = array());

  /** Get all released packages.
   * @param string|array $releases Optionnal list of releases
   * @return Array Contains packageDaos.
   */
  abstract function getReleasedPackages($folderDaos, $releases = array());

} // end class Slicerpackages_PackageModelBase
