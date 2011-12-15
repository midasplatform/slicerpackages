<?php
/*=========================================================================
 MIDAS Server
 Copyright (c) Kitware SAS. 26 rue Louis Guérin. 69100 Villeurbanne, FRANCE
 All rights reserved.
 More information http://www.kitware.com

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/
/*=========================================================================
  MIDAS Server

  Copyright (c) Kitware Inc. All rights reserved.
  See Copyright.txt or http://www.Kitware.com/Copyright.htm for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/**
 * The install script for the api module
 */
class Slicerpackages_InstallScript extends MIDASModuleInstallScript
  {

  /**
   * Pre-install callback does nothing
   */
  public function preInstall()
    {
    }

  /**
   * Post-install callback creates default api keys
   * for all existing users
   */
  public function postInstall()
    {
    include_once BASE_PATH.'/modules/slicerpackages/models/AppModel.php';
    $modelLoader = new MIDAS_ModelLoader();

    $communityModel = $modelLoader->loadModel('Community');
    $folderModel = $modelLoader->loadModel('Folder');
    $folderPolicyGroupModel = $modelLoader->loadModel('Folderpolicygroup');
    $folderPolicyUserModel = $modelLoader->loadModel('Folderpolicyuser');

    if($communityModel->getByName('Slicer'))
      {
      exit();
      }

    $community = $communityModel->createCommunity('Slicer',
                                                  'Community for storing slicer metadata',
                                                  MIDAS_COMMUNITY_PUBLIC,
                                                  NULL,
                                                  true);

    $parent = $community->getPublicFolder();
    $policyGroup = $parent->getFolderpolicygroup();
    $policyUser = $parent->getFolderpolicyuser();

    $packageFolder = $folderModel->createFolder('Packages',
                                                 'Application and Extention Packages',
                                                 $parent);
    $dataFolder = $folderModel->createFolder('Data',
                                              'Publicly avaiable data associated with Slicer',
                                              $parent);

    $applicationsFolder = $folderModel->createFolder('Application',
                                                      'Full application packages',
                                                      $packageFolder);
    $extensionsFolder = $folderModel->createFolder('Extensions',
                                                    'Full application packages',
                                                    $packageFolder);
    $folders = array();
    $folders[] = $packageFolder;
    $folders[] = $dataFolder;
    $folders[] = $applicationsFolder;
    $folders[] = $extensionsFolder;
    $folders[] = $folderModel->createFolder('Nightly',
                                             'For Nightly Builds',
                                             $applicationsFolder);
    $folders[] = $folderModel->createFolder('Experimental',
                                             'For Experimental Builds',
                                             $applicationsFolder);
    $folders[] = $folderModel->createFolder('Nightly',
                                             'For Nightly Builds',
                                             $extensionsFolder);
    $folders[] = $folderModel->createFolder('Experimental',
                                             'For Experimental Builds',
                                             $extensionsFolder);

    // Copy parent permissions to the new folders
    foreach($folders as $folder)
      {
      foreach($policyGroup as $policy)
        {
        $folderPolicyGroupModel->createPolicy($policy->getGroup(), $folder, $policy->getPolicy());
        }
      foreach($policyUser as $policy)
        {
        $folderPolicyUserModel->createPolicy($policy->getUser(), $folder, $policy->getPolicy());
        }
      }
  }
}

?>
