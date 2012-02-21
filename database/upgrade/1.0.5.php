<?php

/**
 * Add screenshots & contributors fields to the extension table
 */
class Slicerpackages_Upgrade_1_0_5 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  /** Mysql upgrade */
  public function mysql()
    {
    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `screenshots` text NOT NULL DEFAULT ''");

    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `contributors` text NOT NULL DEFAULT ''");
    }

  /** Pgsql upgrade */
  public function pgsql()
    {
    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN screenshots text NOT NULL DEFAULT ''");

    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN contributors text NOT NULL DEFAULT ''");
    }

  public function postUpgrade()
    {
    }
}
?>
