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
/** demo overwrite component */
class Slicerpackages_IndexCoreController extends Slicerpackages_AppController
{

  /**
   * Initialization for the controller
   */
  function init()
    {
    } // end method init

  /** index action that redirects to the plugin index */
  function indexAction()
    {
    // 274: Corresponds to folder "Public/Slicer/Packages/Application/Release"
    $this->_redirect('/folder/274');
    } // end method indexAction

}//end class