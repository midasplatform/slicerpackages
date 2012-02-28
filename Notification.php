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

require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';

class Slicerpackages_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'slicerpackages';
  public $_moduleComponents = array('Api');
  public $_models = array();

  /** init notification process*/
  public function init()
    {
    $this->addCallBack('CALLBACK_CORE_GET_LEFT_LINKS', 'getLeftLinks');
    $this->addCallBack('CALLBACK_CORE_ITEM_DELETED', 'itemDeleted');
    $this->addCallBack('CALLBACK_CORE_ITEM_VIEW_ACTIONMENU', 'getItemMenuLink');
    $this->addCallBack('CALLBACK_CORE_GET_FOOTER_HEADER', 'getHeader');
    $this->enableWebAPI($this->moduleName);
    }//end init

  /**
   * Add the link to this module to the left side list
   */
  public function getLeftLinks()
    {
    $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
    $moduleWebroot = $baseUrl.'/'.$this->moduleName;
    return array('Slicer Packages' => array(
      $moduleWebroot.'/view',
      $baseUrl.'/modules/'.$this->moduleName.'/public/images/slicerpackages.png'));
    }

  /**
   * Add link to the right hand menu in the item view
   */
  public function getItemMenuLink($params)
    {
    $item = $params['item'];
    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $packageModel = $modelLoader->loadModel('Package', $this->moduleName);
    $package = $packageModel->getByItemId($item->getKey());

    if(!$itemModel->policyCheck($item, $this->userSession->Dao, MIDAS_POLICY_ADMIN))
      {
      return '';
      }
    if($package)
      {
      $type = 'package';
      $id = $package->getKey();
      }
    else
      {
      $extensionModel = $modelLoader->loadModel('Extension', $this->moduleName);
      $extension = $extensionModel->getByItemId($item->getKey());
      if($extension)
        {
        $type = 'extension';
        $id = $extension->getKey();
        }
      else
        {
        return '';
        }
      }

    $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
    return '<li><a href="'.$baseUrl.'/'.$this->moduleName.'/manage'.$type.'?id='.$id.
           '"><img alt="" src="'.$baseUrl.'/modules/'.$this->moduleName.
           '/public/images/package_go.png" /> '.$this->t('Manage '.$type).'</a></li>';
    }

  /**
   * When an item is deleted, we must delete associated package/extension records
   */
  public function itemDeleted($args)
    {
    $itemDao = $args['item'];
    $modelLoader = new MIDAS_ModelLoader();

    $packageModel = $modelLoader->loadModel('Package', 'slicerpackages');
    $package = $packageModel->getByItemId($itemDao->getKey());
    if($package)
      {
      $packageModel->delete($package);
      }

    $extensionModel = $modelLoader->loadModel('Extension', 'slicerpackages');
    $extension = $extensionModel->getByItemId($itemDao->getKey());
    if($extension)
      {
      $extensionModel->delete($extension);
      }
    }

  /**
   * We use a callback to add the lines to core that we need for view
   * customization.
   */
  public function getHeader()
    {
    $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
    $cssPath = $baseUrl.'/modules/'.$this->moduleName.'/public/css/custom.layout.css';
    $cssHtml = '<link type="text/css" rel="stylesheet" href="'.$cssPath.'">';
    return $cssHtml;
    }

  } //end class

?>
