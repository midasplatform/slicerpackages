<?php

/**
 * Add category field to the extension table
 */
class Slicerpackages_Upgrade_1_0_3 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  /** Mysql upgrade */
  public function mysql()
    {
    // Add category metadata field to extension table
    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `category` varchar(255) NOT NULL DEFAULT ''");

    // Add index on category column for fast category filtering
    $this->db->query("ALTER TABLE `slicerpackages_extension` ADD INDEX (`category`)");
    }

  /** Pgsql upgrade */
  public function pgsql()
    {
    // Add category metadata field to extension table
    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN category character varying(256) NOT NULL DEFAULT ''");

    // Add index on category column for fast category filtering
    $this->db->query("CREATE INDEX slicerpackages_extension_idx_category
                      ON slicerpackages_extension (category)");
    }

  public function postUpgrade()
    {
    }
}
?>
