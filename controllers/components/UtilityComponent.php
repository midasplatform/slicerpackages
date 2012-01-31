<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware Inc. All rights reserved.
101 East Weaver St,
Carrboro, North Carolina
27510  USA

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/

/** Component for api methods */
class Slicerpackages_UtilityComponent extends AppComponent
{
  /**
   * Get folderDao for given packageType ('Package' or 'Extension')
   * and submissionType ('experimental' or 'nightly').
   */
  function getPackageFolder($userDao, $packagetype, $submissiontype)
    {
    $modelLoader = new MIDAS_ModelLoader();
    $settingModel = $modelLoader->loadModel('Setting');
    $folderModel = $modelLoader->loadModel('Folder');

    $key = strtolower($packagetype).'s.'.$submissiontype.'.folder';
    $folderId = $settingModel->getValueByName($key, 'slicerpackages');
    if(!$folderId || !is_numeric($folderId))
      {
      throw new Exception('You must configure a folder id for key '.$key, -1);
      }
    $folder = $folderModel->load($folderId);
    if(!$folder)
      {
      throw new Exception('Folder with id '.$folderId.' does not exist', -1);
      }
    if(!$folderModel->policyCheck($folder, $userDao, MIDAS_POLICY_READ))
      {
      throw new Exception('Invalid policy on folder '.$folderId, -1);
      }
    return $folder;
    }

  /**
   * Get list of release identifiers associated with the given folderDaos.
   * By default release identifiers will be sorted in descendant order.
   */
  function getReleaseIdentifiers($folderDaos, $desc = true)
    {
    if(!is_array($folderDaos))
      {
      $folderDaos = array($folderDaos);
      }
    $modelLoader = new MIDAS_ModelLoader();
    $packageModel = $modelLoader->loadModel('Package', 'slicerpackages');
    $packageModel->loadDaoClass('PackageDao', 'slicerpackages');

    $packageDaos = $packageModel->getReleasedPackages($folderDaos);
    $releaseNames = array_unique(array_map(function($packageDao){ return $packageDao->getRelease(); }, $packageDaos));

    usort($releaseNames, 'version_compare');

    return $desc ? array_reverse($releaseNames, false) : $releaseNames;
    }

} // end class
