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

/** Module configure controller */
class Slicerpackages_ConfigController extends Slicerpackages_AppController
{
  public $_moduleForms = array('Config');
  public $_components = array();

  /** index action */
  function indexAction()
    {
    if(!$this->logged || !$this->userSession->Dao->getAdmin() == 1)
      {
      throw new Zend_Exception('You should be an administrator');
      }

    $modelLoader = new MIDAS_ModelLoader();
    $settingModel = $modelLoader->loadModel('Setting');

    $packagesNightlyFolder = $settingModel->getValueByName('packages.nightly.folder', $this->moduleName);
    $packagesExperimentalFolder = $settingModel->getValueByName('packages.experimental.folder', $this->moduleName);

    $extensionsNightlyFolder = $settingModel->getValueByName('extensions.nightly.folder', $this->moduleName);
    $extensionsContinuousFolder = $settingModel->getValueByName('extensions.continuous.folder', $this->moduleName);
    $extensionsExperimentalFolder = $settingModel->getValueByName('extensions.experimental.folder', $this->moduleName);

    $configForm = $this->ModuleForm->Config->createConfigForm();

    $formArray = $this->getFormAsArray($configForm);
    if($packagesNightlyFolder)
      {
      $formArray['packagesnightlyfolder']->setValue($packagesNightlyFolder);
      }
    if($packagesExperimentalFolder)
      {
      $formArray['packagesexperimentalfolder']->setValue($packagesExperimentalFolder);
      }
    if($extensionsNightlyFolder)
      {
      $formArray['extensionsnightlyfolder']->setValue($extensionsNightlyFolder);
      }
    if($extensionsContinuousFolder)
      {
      $formArray['extensionscontinuousfolder']->setValue($extensionsContinuousFolder);
      }
    if($extensionsExperimentalFolder)
      {
      $formArray['extensionsexperimentalfolder']->setValue($extensionsExperimentalFolder);
      }
    $this->view->configForm = $formArray;

    if($this->_request->isPost())
      {
      $this->disableLayout();
      $this->_helper->viewRenderer->setNoRender();
      $submitConfig = $this->_getParam('submitConfig');
      if(isset($submitConfig))
        {
        $settingModel->setConfig('packages.nightly.folder', $this->_getParam('packagesnightlyfolder'), $this->moduleName);
        $settingModel->setConfig('packages.experimental.folder', $this->_getParam('packagesexperimentalfolder'), $this->moduleName);
        $settingModel->setConfig('extensions.nightly.folder', $this->_getParam('extensionsnightlyfolder'), $this->moduleName);
        $settingModel->setConfig('extensions.continuous.folder', $this->_getParam('extensionscontinuousfolder'), $this->moduleName);
        $settingModel->setConfig('extensions.experimental.folder', $this->_getParam('extensionsexperimentalfolder'), $this->moduleName);

        echo JsonComponent::encode(array(true, 'Changes saved'));
        }
      }
    }

}//end class
