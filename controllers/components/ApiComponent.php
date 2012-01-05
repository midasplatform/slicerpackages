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

/** Component for api methods */
class Slicerpackages_ApiComponent extends AppComponent
{

  /**
   * Helper function for verifying keys in an input array
   */
  private function _checkKeys($keys, $values)
    {
    foreach($keys as $key)
      {
      if(!array_key_exists($key, $values))
        {
        throw new Exception('Parameter '.$key.' must be set.', -1);
        }
      }
    }

  /**
   * Helper function to get the user from token or session authentication
   */
  private function _getUser($args)
    {
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    return $authComponent->getUser($args, null);
    }

  /**
   * Read in the streamed uploaded file and write it to a temporary file.
   * Returns the name of the temporary file.
   */
  private function _readUploadedFile($prefix)
    {
    set_time_limit(0);
    $inputfile = 'php://input';
    $tmpfile = tempnam(BASE_PATH.'/tmp/misc', $prefix);
    $in = fopen($inputfile, 'rb');
    $out = fopen($tmpfile, 'wb');

    $bufSize = 1024 * 1024;

    $size = 0;
    // read from input and write into file
    while(connection_status() == CONNECTION_NORMAL && ($buf = fread($in, $bufSize)))
      {
      $size += strlen($buf);
      fwrite($out, $buf);
      }
    fclose($in);
    fclose($out);

    return $tmpfile;
    }

  /**
   * Upload a core package
   * @param os The target operating system of the package
   * @param arch The os chip architecture (i386, amd64, etc)
   * @param name The name of the package (ie installer name)
   * @param revision The svn or git revision of the installer
   * @param submissiontype Whether this is from a nightly, experimental, continuous, etc dashboard
   * @param packagetype Installer, data, etc
   * @param productname The product name (Ex: Slicer)
   * @param codebase The codebase name (Ex: Slicer4)
   * @param release (Optional) Release identifier (Ex: 4.0.0, 4.2)
   * @param checkoutdate (Optional) The date of the checkout
   * @return Status of the upload
   */
  public function uploadPackage($args)
    {
    $this->_checkKeys(array('os',
                            'arch',
                            'name',
                            'revision',
                            'submissiontype',
                            'packagetype',
                            'productname',
                            'codebase'), $args);

    $userDao = $this->_getUser($args);
    if($userDao === false)
      {
      throw new Exception('Invalid user authentication', -1);
      }

    $tmpfile = $this->_readUploadedFile('slicerpackage');

    $modelLoader = new MIDAS_ModelLoader();
    $settingModel = $modelLoader->loadModel('Setting');
    $folderModel = $modelLoader->loadModel('Folder');
    $key = 'packages.'.$args['submissiontype'].'.folder';
    $folderId = $settingModel->getValueByName($key, 'slicerpackages');

    if(!$folderId || !is_numeric($folderId))
      {
      unlink($tmpfile);
      throw new Exception('You must configure a folder id for key '.$key, -1);
      }
    $folder = $folderModel->load($folderId);

    if(!$folder)
      {
      unlink($tmpfile);
      throw new Exception('Folder with id '.$folderId.' does not exist', -1);
      }
    if(!$folderModel->policyCheck($folder, $userDao, MIDAS_POLICY_WRITE))
      {
      unlink($tmpfile);
      throw new Exception('Invalid policy on folder '.$folderId, -1);
      }
    $componentLoader = new MIDAS_ComponentLoader();
    $uploadComponent = $componentLoader->loadComponent('Upload');
    $item = $uploadComponent->createUploadedItem($userDao, $args['name'], $tmpfile, $folder);

    if(!$item)
      {
      throw new Exception('Failed to create item', -1);
      }
    $packageModel = $modelLoader->loadModel('Package', 'slicerpackages');
    $packageModel->loadDaoClass('PackageDao', 'slicerpackages');
    $packageDao = new Slicerpackages_PackageDao();
    $packageDao->setItemId($item->getKey());
    $packageDao->setSubmissiontype($args['submissiontype']);
    $packageDao->setPackagetype($args['packagetype']);
    $packageDao->setOs($args['os']);
    $packageDao->setArch($args['arch']);
    $packageDao->setRevision($args['revision']);
    $packageDao->setProductname($args['productname']);
    $packageDao->setCodebase($args['codebase']);
    if(array_key_exists('release', $args))
      {
      $packageDao->setRelease($args['release']);
      }
    if(array_key_exists('checkoutdate', $args))
      {
      $packageDao->setCheckoutdate($args['checkoutdate']);
      }
    $packageModel->save($packageDao);

    return array('package' => $packageDao);
    }

  /**
   * Upload an extension package
   * @param os The target operating system of the package
   * @param arch The os chip architecture (i386, amd64, etc)
   * @param name The name of the package (ie installer name)
   * @param repository_url The url of the repository
   * @param revision The svn or git revision of the extension
   * @param slicer_revision The revision of Slicer that the extension was built against
   * @param submissiontype Whether this is from a nightly, experimental, continuous, etc dashboard
   * @param packagetype Installer, data, etc
   * @param productname The product name (Ex: Slicer)
   * @param codebase The codebase name (Ex: Slicer4)
   * @param icon_url (Optional) The url of the icon for the extension
   * @param development_status (Optional) Arbitrary description of the status of the extension (stable, active, etc)
   * @return Status of the upload
   */
  public function uploadExtension($args)
    {
    $this->_checkKeys(array('os',
                            'arch',
                            'name',
                            'revision',
                            'repository_url',
                            'slicer_revision',
                            'submissiontype',
                            'packagetype',
                            'productname',
                            'codebase'), $args);

    $userDao = $this->_getUser($args);
    if($userDao === false)
      {
      throw new Exception('Invalid user authentication', -1);
      }

    $tmpfile = $this->_readUploadedFile('slicerextension');

    $modelLoader = new MIDAS_ModelLoader();
    $settingModel = $modelLoader->loadModel('Setting');
    $folderModel = $modelLoader->loadModel('Folder');
    $key = 'extensions.'.$args['submissiontype'].'.folder';
    $folderId = $settingModel->getValueByName($key, 'slicerpackages');

    if(!$folderId || !is_numeric($folderId))
      {
      unlink($tmpfile);
      throw new Exception('You must configure a folder id for key '.$key, -1);
      }
    $folder = $folderModel->load($folderId);

    if(!$folder)
      {
      unlink($tmpfile);
      throw new Exception('Folder with id '.$folderId.' does not exist', -1);
      }
    if(!$folderModel->policyCheck($folder, $userDao, MIDAS_POLICY_WRITE))
      {
      unlink($tmpfile);
      throw new Exception('Invalid policy on folder '.$folderId, -1);
      }
    $componentLoader = new MIDAS_ComponentLoader();
    $uploadComponent = $componentLoader->loadComponent('Upload');
    $item = $uploadComponent->createUploadedItem($userDao, $args['name'], $tmpfile, $folder);

    unlink($tmpfile);

    if(!$item)
      {
      throw new Exception('Failed to create item', -1);
      }
    $extensionModel = $modelLoader->loadModel('Extension', 'slicerpackages');
    $extensionModel->loadDaoClass('ExtensionDao', 'slicerpackages');
    $extensionDao = new Slicerpackages_ExtensionDao();
    $extensionDao->setItemId($item->getKey());
    $extensionDao->setSubmissiontype($args['submissiontype']);
    $extensionDao->setPackagetype($args['packagetype']);
    $extensionDao->setOs($args['os']);
    $extensionDao->setArch($args['arch']);
    $extensionDao->setRevision($args['revision']);
    $extensionDao->setSlicerRevision($args['slicer_revision']);
    $extensionDao->setProductname($args['productname']);
    $extensionDao->setCodebase($args['codebase']);
    if(array_key_exists('icon_url', $args))
      {
      $extensionDao->setIconUrl($args['icon_url']);
      }
    if(array_key_exists('development_status', $args))
      {
      $extensionDao->setDevelopmentStatus($args['development_status']);
      }
    $extensionModel->save($extensionDao);

    return array('extension' => $extensionDao);
    }

  /**
   * Get a filtered list of available Slicer packages
   * @param os (Optional) The target operating system of the package (linux | win | macosx)
   * @param arch (Optional) The os chip architecture (i386 | amd64)
   * @param submissiontype (Optional) Dashboard model used to submit (nightly | experimental | continuous)
   * @param packagetype (Optional) The package type (installer | data | extension)
   * @param productname (Optional) The product name (Example: Slicer)
   * @param codebase (Optional) The codebase name (Example: Slicer4)
   * @param revision (Optional) The revision of the package
   * @param order (Optional) What parameter to order results by (revision | packagetype | submissiontype | arch | os)
   * @param direction (Optional) What direction to order results by (asc | desc).  Default asc
   * @param limit (Optional) Limit result count. Must be a positive integer.
   * @param release (Optional) Set to true to return only release packages, or false to return only non-release packages.
     If not set, it will return both.
   * @return An array of slicer packages
   */
  public function getPackages($args)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $packagesModel = $modelLoad->loadModel('Package', 'slicerpackages');
    $packagesModel->loadDaoClass('PackageDao', 'slicerpackages');
    $itemModel = $modelLoad->loadModel('Item');

    $daos = $packagesModel->get($args);

    $results = array();
    foreach($daos as $dao)
      {
      $revision = $itemModel->getLastRevision($dao->getItem());
      $bitstreams = $revision->getBitstreams();
      $bitstreamsArray = array();
      foreach($bitstreams as $bitstream)
        {
        $bitstreamsArray[] = array('bitstream_id' => $bitstream->getKey(),
                                   'name' => $bitstream->getName(),
                                   'md5' => $bitstream->getChecksum(),
                                   'size' => $bitstream->getSizebytes());
        }

      $results[] = array('package_id' => $dao->getKey(),
                         'item_id' => $dao->getItemId(),
                         'os' => $dao->getOs(),
                         'arch' => $dao->getArch(),
                         'revision' => $dao->getRevision(),
                         'submissiontype' => $dao->getSubmissiontype(),
                         'package' => $dao->getPackagetype(),
                         'release' => $release,
                         'name' => $dao->getItem()->getName(),
                         'productname' =>$dao->getProductname(),
                         'codebase' => $dao->getCodebase(),
                         'release' => $dao->getRelease(),
                         'checkoutdate' => $dao->getCheckoutdate(),
                         'date_creation' => $dao->getItem()->getDateCreation(),
                         'bitstreams' => $bitstreamsArray);
      }
    return $results;
    }

  /**
   * Get a filtered list of available Slicer extensions
   * @param os (Optional) The target operating system of the package (linux | win | macosx)
   * @param arch (Optional) The os chip architecture (i386 | amd64)
   * @param submissiontype (Optional) Dashboard model used to submit (nightly | experimental | continuous)
   * @param packagetype (Optional) The package type (installer | data | extension)
   * @param productname (Optional) The product name (Example: Slicer)
   * @param codebase (Optional) The codebase name (Example: Slicer4)
   * @param revision (Optional) The revision of the package
   * @param slicer_revision (Optional) The slicer revision the package was built against
   * @param order (Optional) What parameter to order results by (revision | packagetype | submissiontype | arch | os)
   * @param direction (Optional) What direction to order results by (asc | desc).  Default asc
   * @param limit (Optional) Limit result count. Must be a positive integer.
   * @param release (Optional) Set to true to return only release packages, or false to return only non-release packages.
     If not set, it will return both.
   * @return An array of slicer extension daos
   */
  public function getExtensions($args)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $extensionsModel = $modelLoad->loadModel('Extension', 'slicerpackages');
    $extensionsModel->loadDaoClass('ExtensionDao', 'slicerpackages');
    $itemModel = $modelLoad->loadModel('Item');

    $daos = $extensionsModel->get($args);

    $results = array();
    foreach($daos as $dao)
      {
      $revision = $itemModel->getLastRevision($dao->getItem());
      $bitstreams = $revision->getBitstreams();
      $bitstreamsArray = array();
      foreach($bitstreams as $bitstream)
        {
        $bitstreamsArray[] = array('bitstream_id' => $bitstream->getKey(),
                                   'name' => $bitstream->getName(),
                                   'md5' => $bitstream->getChecksum(),
                                   'size' => $bitstream->getSizebytes());
        }

      $results[] = array('extension_id' => $dao->getKey(),
                         'item_id' => $dao->getItemId(),
                         'os' => $dao->getOs(),
                         'arch' => $dao->getArch(),
                         'revision' => $dao->getRevision(),
                         'submissiontype' => $dao->getSubmissiontype(),
                         'package' => $dao->getPackagetype(),
                         'release' => $release,
                         'name' => $dao->getItem()->getName(),
                         'productname' =>$dao->getProductname(),
                         'codebase' => $dao->getCodebase(),
                         'release' => $dao->getRelease(),
                         'checkoutdate' => $dao->getCheckoutdate(),
                         'date_creation' => $dao->getItem()->getDateCreation(),
                         'bitstreams' => $bitstreamsArray);
      }
    return $results;
    }

} // end class
