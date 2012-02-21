<?php

/**
 * Add description, enabled, homepage and repository_type fields to the extension table
 */
class Slicerpackages_Upgrade_1_0_4 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  /** Mysql upgrade */
  public function mysql()
    {
    // Add description metadata field to extension table
    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `description` text NOT NULL DEFAULT ''");

    // Add enabled metadata field to extension table
    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `enabled` tinyint(1) NOT NULL DEFAULT '1'");

    // Add homepage metadata field to extension table
    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `homepage` text NOT NULL DEFAULT ''");

    // Add repository_type metadata field to extension table
    $this->db->query("ALTER TABLE `slicerpackages_extension`
                      ADD COLUMN `repository_type` varchar(10) NOT NULL DEFAULT ''");
    }

  /** Pgsql upgrade */
  public function pgsql()
    {
    // Add description metadata field to extension table
    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN description text NOT NULL DEFAULT ''");

    // Add enabled metadata field to extension table
    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN enabled integer NOT NULL DEFAULT 1");

    // Add homepage metadata field to extension table
    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN homepage text NOT NULL DEFAULT ''");

    // Add repository_type metadata field to extension table
    $this->db->query("ALTER TABLE slicerpackages_extension
                      ADD COLUMN repository_type character varying(10) NOT NULL DEFAULT ''");
    }

  public function postUpgrade()
    {
    }
}
?>
