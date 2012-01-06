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
/** demo controller*/
class Slicerpackages_ViewController extends Slicerpackages_AppController
{

  public $_models = array('User', 'Bitstream', 'Item', 'Folder', 'Community', 'Folderpolicyuser', 'Folderpolicygroup');
  public $_moduleModels = array('Package', 'Extension');
  public $_daos = array('User', 'Bitstream', 'Item', 'Folder', 'Community');
  public $_moduleDaos = array('Package', 'Extension');
  public $_components = array('Date', 'Utility');
  public $_moduleComponents = array();
  public $_forms = array();
  public $_moduleForms = array();

  /** Helper function allowing to generate breadcrumb */
  private function _breadcrumb($subfolder = '', $name = '')
    {
    // TODO Generalize the concept of 'breadcrumb' for plugins ? Look at Zend BreadCrumb ?
    $breadcrumb  = '<link type="text/css" rel="stylesheet" href="'.$this->view->coreWebroot.'/public/css/common/common.browser.css" />';
    $breadcrumb .= '<link type="text/css" rel="stylesheet" href="'.$this->view->coreWebroot.'/public/css/folder/folder.view.css" />';
    $breadcrumb .= '<ul class="pathBrowser">';
    $breadcrumb .= '  <li class ="pathCommunity"><img alt = "" src = "'.$this->view->moduleWebroot.'/public/images/'.$this->moduleName.'.png" /><span><a href="'.$this->view->webroot.'/'.$this->moduleName.'/view">&nbsp;'.$this->view->moduleFullName.'</a></span></li>';
    if($subfolder != '')
      {
      if($name == '')
        {
        $name = $subfolder;
        }
      $breadcrumb .= '  <li class ="pathFolder"><img alt = "" src = "'.$this->view->moduleWebroot.'/public/images/'.$this->moduleName.'_'.$subfolder.'.png" /><span><a href="'.$this->view->webroot.'/'.$this->moduleName.'/view/'.$subfolder.'">&nbsp;'.$name.'</a></span></li>';
      }
    $breadcrumb .= '</ul>';
    return $breadcrumb;
    }

  private function _collectReleasesByFolder(&$releaseSets, $packagetype, $folderDaos)
    {
    $daoName = 'Slicerpackages_'.$packagetype;
    $packageDao = $this->$daoName;

    if(!is_array($folderDaos))
      {
      $folderDaos = array($folderDaos);
      }

    foreach($folderDaos as $folder)
      {
      foreach($folder->getItems() as $item)
        {
        $package = $packageDao->getByItemId($item->getKey());
        $release = $package->getRelease();
        if (!empty($release))
          {
          if(!isset($releaseSets[$release]))
            {
            $releaseSets[$release] = array();
            }
          if(!isset($releaseSets[$release][$package->getOs()]))
            {
            $releaseSets[$release][$package->getOs()] = array();
            }
          if(!isset($releaseSets[$release][$package->getOs()][$item->getName()]))
            {
            $releaseSets[$release][$package->getOs()][$item->getName()] = array();
            }

          $releaseSets[$release][$package->getOs()][$item->getName()] = $package;
          }
        }
      }
    }

  private function _collectPackage(&$releaseSets, $releaseName, $packageDao, $itemDao = null)
    {
    if(!$itemDao instanceof ItemDao)
      {
      $itemDaos = $this->Item->findBy('item_id', $packageDao->getItemId());
      $itemDao = $itemDaos[0];
      }
    $os = $packageDao->getOs();
    if(!isset($releaseSets[$os]))
      {
      $releaseSets[$os] = array();
      }
    if(!isset($releaseSets[$os][$releaseName]))
      {
      $releaseSets[$os][$releaseName] = array();
      }
    $arch = $packageDao->getArch();
    if(!isset($releaseSets[$os][$releaseName][$arch]))
      {
      $releaseSets[$os][$releaseName][$arch] = array();
      }
    $releaseSets[$os][$releaseName][$arch]['dao'] = $packageDao;
    $releaseSets[$os][$releaseName][$arch]['size'] = $this->Component->Utility->formatSize($itemDao->getSizebytes());
    $releaseSets[$os][$releaseName][$arch]['date_creation'] = date('D, j M Y', strtotime($itemDao->get('date_creation')));
    $releaseSets[$os][$releaseName][$arch]['lastupdated'] =
       $this->Component->Date->ago($itemDao->get('date_creation'));
    if($packageDao instanceof Slicerpackages_PackageDao)
      {
      $getExtensionsFilter = array(
        'slicer_revision' => $packageDao->getRevision(),
        'arch' => $packageDao->getArch(),
        'os' => $packageDao->getOs()
        );
      $releaseSets[$os][$releaseName][$arch]['extensioncount'] =
        count($this->Slicerpackages_Extension->get($getExtensionsFilter));
      }
    $bitstreams = $this->Item->getLastRevision($itemDao)->getBitstreams();
    $releaseSets[$os][$releaseName][$arch]['checksum'] =strtoupper($bitstreams[0]->getChecksum());
    }

  private function _getPackageFolder($packagetype, $submissiontype)
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
    if(!$folderModel->policyCheck($folder, $this->userSession->Dao, MIDAS_POLICY_READ))
      {
      throw new Exception('Invalid policy on folder '.$folderId, -1);
      }
    return $folder;
    }

  private function _collectReleases(&$releaseSets, $packagetype, $folderDaos)
    {
    $instanceName = ucfirst($this->moduleName).'_'.$packagetype;
    $packageModel = $this->$instanceName;

    if(!is_array($folderDaos))
      {
      $folderDaos = array($folderDaos);
      }

    $packageDaos = $packageModel->getReleasedPackages($folderDaos);
    foreach($packageDaos as $packageDao)
      {
      $releaseName = $packageDao->getRelease();
      if (!empty($releaseName))
        {
        $this->_collectPackage($releaseSets, $releaseName, $packageDao);
        }
      }
    }

  private function _collectLatest(&$releaseSets, $packagetype, $folderDaos, $label)
    {
    $instanceName = ucfirst($this->moduleName).'_'.$packagetype;
    $packageModel = $this->$instanceName;

    $mostRecentCreatedItems = $packageModel->getMostRecentCreatedItemsByOsAndArch($folderDaos);

    foreach($mostRecentCreatedItems as $key => $row)
      {
      $packageDaos = $packageModel->findBy($packageModel->getKey(), $row[$packageModel->getKey()]);
      $this->_collectPackage($releaseSets, $label, $packageDaos[0]);
      }
    }

  private function _getApplicationReleaseNames($folderDaos)
    {
    if(!is_array($folderDaos))
      {
      $folderDaos = array($folderDaos);
      }
    $packagetype = 'Package';
    $instanceName = ucfirst($this->moduleName).'_'.$packagetype;
    $packageModel = $this->$instanceName;

    $packageDaos = $packageModel->getReleasedPackages($folderDaos);
    $releaseNames = array_unique(array_map(function($packageDao){ return $packageDao->getRelease(); }, $packageDaos));

    usort($releaseNames, 'version_compare');
    return array_reverse($releaseNames, true);
    }

  private function _initializeOsAndArchMap($moduleName, $view)
    {
    $view->json[$moduleName]['latest_category_text'] = self::LatestCategoryText;

    $view->os_shortname_to_longname = array( 'linux' => 'GNU/Linux', 'macosx' => 'Mac OSX', 'win' => 'Windows');
    $view->available_oss = array_keys($view->os_shortname_to_longname);
    $view->os_longname_to_shortname = array_flip($view->os_shortname_to_longname);
    $view->json[$moduleName]['os_longname_to_shortname'] = $view->os_longname_to_shortname;
    $view->json[$moduleName]['os_shortname_to_longname'] = $view->os_shortname_to_longname;

    $view->arch_shortname_to_longname = array('i386' => '32-bit', 'amd64' => '64-bit');
    $view->available_archs = array_keys($view->arch_shortname_to_longname);
    $view->arch_longname_to_shortname = array_flip($view->arch_shortname_to_longname);
    $view->json[$moduleName]['arch_longname_to_shortname'] = $view->arch_longname_to_shortname;
    $view->json[$moduleName]['arch_shortname_to_longname'] = $view->arch_shortname_to_longname;
    }

  /** Name of the category used to list both latest experimental and nightly submissions */
  const LatestCategoryText = 'Nightly';

  /** index action */
  public function indexAction()
    {
    $this->view->header = $this->_breadcrumb();
    $this->view->nPackages = $this->Slicerpackages_Package->getCountAll();

    $this->view->deprecatedLayout = $this->getRequest()->getParam("deprecatedLayout", false);
    $this->view->deprecatedLayout = $this->view->deprecatedLayout == 'true' ? true : false;
    $folderDaos = array(
      $this->_getPackageFolder('Package', 'nightly'),
      $this->_getPackageFolder('Package', 'experimental'));
    if($this->view->deprecatedLayout)
      {
      $packageSets = array();
      $this->_collectReleasesByFolder($packageSets, 'Package', $folderDaos);
      uksort($packageSets, 'version_compare');
      $this->view->packageSets = array_reverse($packageSets, true);
      }
    else
      {
      $packageSetsByOs = array();
      $this->_collectReleases($packageSetsByOs, 'Package', $folderDaos);
      $this->_collectLatest($packageSetsByOs, 'Package', $folderDaos, self::LatestCategoryText);
      foreach($packageSetsByOs as $os => &$packageSuperset)
        {
        uksort($packageSuperset, 'version_compare');
        $packageSuperset = array_reverse($packageSuperset, true);
        foreach($packageSuperset as $arch => &$packageSet)
          {
          ksort($packageSet);
          }
          // The following code enforce the 'self::LatestCategoryText' category to be at the top
  //        $packageSupersetKeys = array_keys($packageSuperset);
  //        if($packageSupersetKeys[count($packageSupersetKeys) - 1] == self::LatestCategoryText)
  //          {
  //          $packageSuperset = array(self::LatestCategoryText => array_pop($packageSuperset)) + $packageSuperset;
  //          }
        }

      $mostRecentCreatedItem = $this->Slicerpackages_Package->getMostRecentCreatedItem($folderDaos);
      if(array_key_exists('date_creation', $mostRecentCreatedItem))
        {
        $this->view->lastupdated =  $this->Component->Date->ago($mostRecentCreatedItem['date_creation']);
        }
      else
        {
        $this->view->lastupdated = 'NA';
        }

      $this->_initializeOsAndArchMap($this->moduleName, $this->view);
      $this->view->packageSetsByOs = $packageSetsByOs;
      $this->view->latestCategoryText = self::LatestCategoryText;
      }
    }

  /** Admin action */
  public function adminAction()
    {
    $this->view->nPackages = $this->Slicerpackages_Package->getCountAll();
    }

  /** action for view/advanced (the package search page) */
  public function advancedAction()
    {
    $avalue = function($k, $a, $default) { return array_key_exists($k, $a) ? $a[$k] : $default; };

    $this->view->json[$this->moduleName]['requested_arch'] = $avalue('arch', $_GET, '');
    $this->view->json[$this->moduleName]['requested_os'] = $avalue('os', $_GET, '');
    $this->view->json[$this->moduleName]['requested_packagetype'] = $avalue('packagetype', $_GET, '');
    $this->view->json[$this->moduleName]['requested_slicer_revision'] = $avalue('slicer_revision', $_GET, '');
    $this->view->json[$this->moduleName]['requested_release'] = $avalue('release', $_GET, '');

    $this->_initializeOsAndArchMap($this->moduleName, $this->view);

    $folderDaos = array(
      $this->_getPackageFolder('Package', 'nightly'),
      $this->_getPackageFolder('Package', 'experimental'));
    $this->view->releaseNames = $this->_getApplicationReleaseNames($folderDaos);
    $this->view->releaseNames[] = self::LatestCategoryText;

    $this->view->header = $this->_breadcrumb("advanced", "Additional Download");
    $this->view->nPackages = $this->Slicerpackages_Package->getCountAll();
    $community = $this->Community->getByName('Slicer');
    }

}//end class
