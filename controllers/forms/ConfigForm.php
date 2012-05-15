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
/** Form for configuring the statistics module */
class Slicerpackages_ConfigForm extends AppForm
{
  /** create form */
  public function createConfigForm()
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/slicerpackages/config/index')
         ->setMethod('post');

    $packagesNightlyFolder = new Zend_Form_Element_Text('packagesnightlyfolder');
    $packagesExperimentalFolder = new Zend_Form_Element_Text('packagesexperimentalfolder');
    $extensionsNightlyFolder = new Zend_Form_Element_Text('extensionsnightlyfolder');
    $extensionsContinuousFolder = new Zend_Form_Element_Text('extensionscontinuousfolder');
    $extensionsExperimentalFolder = new Zend_Form_Element_Text('extensionsexperimentalfolder');

    $submit = new Zend_Form_Element_Submit('submitConfig');
    $submit->setLabel('Save configuration');

    $form->addElements(array($packagesNightlyFolder, $packagesExperimentalFolder,
                             $extensionsNightlyFolder, $extensionsContinuousFolder,
                             $extensionsExperimentalFolder, $submit));
    return $form;
    }

} // end class
?>
