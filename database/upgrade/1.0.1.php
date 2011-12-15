<?php

/**
 * We added a few metadata fields to the packages table
 *  -Product name
 *  -Codebase name
 *  -Repository checkout date used to build the package
 */
class Slicerpackages_Upgrade_1_0_1 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  /** Mysql upgrade */
  public function mysql()
    {
    $this->db->query("ALTER TABLE `slicerpackages_package`
                      ADD COLUMN `productname` varchar(255) NOT NULL DEFAULT ''");
    $this->db->query("ALTER TABLE `slicerpackages_package`
                      ADD COLUMN `codebase` varchar(255) NOT NULL DEFAULT ''");
    $this->db->query("ALTER TABLE `slicerpackages_package`
                      ADD COLUMN `checkoutdate` timestamp NULL DEFAULT NULL");
    }

  /** Pgsql upgrade */
  public function pgsql()
    {
    $this->db->query("ALTER TABLE slicerpackages_package
                      ADD COLUMN productname character varying(256) NOT NULL DEFAULT ''");
    $this->db->query("ALTER TABLE slicerpackages_package
                      ADD COLUMN codebase character varying(256) NOT NULL DEFAULT ''");
    $this->db->query("ALTER TABLE slicerpackages_package
                      ADD COLUMN checkoutdate timestamp without time zone NULL DEFAULT NULL");
    }

  public function postUpgrade()
    {
    }
}
?>
