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
 * Slicerpackages Extension Model Base
 */
abstract class Slicerpackages_ExtensionModelBase extends Slicerpackages_AppModel
{
  /** constructor*/
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'slicerpackages_extension';
    $this->_key = 'slicerpackages_extension_id';

    $this->_mainData = array(
        'slicerpackages_extension_id' => array('type' => MIDAS_DATA),
        'item_id' => array('type' => MIDAS_DATA),
        'os' => array('type' => MIDAS_DATA),
        'arch' => array('type' => MIDAS_DATA),
        'repository_url' => array('type' => MIDAS_DATA),
        'revision' => array('type' => MIDAS_DATA),
        'submissiontype' => array('type' => MIDAS_DATA),
        'packagetype' => array('type' => MIDAS_DATA),
        'slicer_revision' => array('type' => MIDAS_DATA),
        'icon_url' => array('type' => MIDAS_DATA),
        'release' => array('type' => MIDAS_DATA),
        'productname' => array('type' => MIDAS_DATA),
        'codebase' => array('type' => MIDAS_DATA),
        'development_status' => array('type' => MIDAS_DATA),
        'category' => array('type' => MIDAS_DATA),
        'item' => array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Item',
                        'parent_column' => 'item_id',
                        'child_column' => 'item_id'),
      );
    $this->initialize(); // required
    } // end __construct()

  public abstract function getAll();
  public abstract function getByItemId($itemId);

} // end class Slicerpackages_ExtensionModelBase
