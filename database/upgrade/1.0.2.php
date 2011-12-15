<?php

/**
 * Add tables for extensions.
 * Also make settings table so that we are agnostic of folder structure.
 * Also add an indexed release metadata field to the package table.
 */
class Slicerpackages_Upgrade_1_0_2 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  /** Mysql upgrade */
  public function mysql()
    {
    // Create extension metadata table
    $this->db->query("CREATE TABLE `slicerpackages_extension` (
      `slicerpackages_extension_id` bigint(20) NOT NULL AUTO_INCREMENT,
      `item_id` bigint(20) NOT NULL,
      `os` VARCHAR(255) NOT NULL,
      `arch` VARCHAR(255) NOT NULL,
      `repository_url` VARCHAR(255) NOT NULL,
      `revision` VARCHAR(255) NOT NULL,
      `submissiontype` VARCHAR(255) NOT NULL,
      `packagetype` VARCHAR(255) NOT NULL,
      `slicer_revision` VARCHAR(255) NOT NULL,
      `release` VARCHAR(255) NOT NULL,
      `icon_url` TEXT NOT NULL DEFAULT '',
      `productname` VARCHAR(255) NOT NULL,
      `codebase` VARCHAR(255) NOT NULL,
      `development_status` TEXT NOT NULL DEFAULT '',
      PRIMARY KEY (`slicerpackages_extension_id`)
      )");

    // Create extension dependency table
    $this->db->query("CREATE TABLE `slicerpackages_extensiondependency` (
      `slicerpackages_extension_name` VARCHAR(255) NOT NULL,
      `slicerpackages_extension_dependency` VARCHAR(255) NOT NULL
      )");

    // Create slicer version compatibility table
    $this->db->query("CREATE TABLE `slicerpackages_extensioncompatibility` (
      `slicerpackages_extension_id` bigint(20) NOT NULL,
      `slicer_revision` VARCHAR(255) NOT NULL
      )");

    // Add release metadata field to packages table
    $this->db->query("ALTER TABLE `slicerpackages_package`
                      ADD COLUMN `release` varchar(255) NOT NULL DEFAULT ''");

    // Add index on release column to quickly grab releases
    $this->db->query("ALTER TABLE `slicerpackages_package` ADD INDEX (`release`)");
    $this->db->query("ALTER TABLE `slicerpackages_extension` ADD INDEX (`release`)");
    }

  /** Pgsql upgrade */
  public function pgsql()
    {
    // Create extension metadata table
    $this->db->query("CREATE TABLE slicerpackages_extension (
      slicerpackages_extension_id serial PRIMARY KEY,
      item_id bigint NOT NULL,
      os character varying(256) NOT NULL,
      arch character varying(256) NOT NULL,
      repository_url character varying(256) NOT NULL,
      revision character varying(256) NOT NULL,
      submissiontype character varying(256) NOT NULL,
      packagetype character varying(256) NOT NULL,
      slicer_revision character varying(256) NOT NULL,
      release character varying(256) NOT NULL,
      icon_url TEXT NOT NULL DEFAULT '',
      productname character varying(256) NOT NULL,
      codebase character varying(256) NOT NULL,
      development_status TEXT NOT NULL DEFAULT ''
      )");

    // Create extension dependency table
    $this->db->query("CREATE TABLE slicerpackages_extensiondependency (
      slicerpackages_extension_name character varying(256) NOT NULL,
      slicerpackages_extension_dependency character varying(256) NOT NULL
      )");

    // Create slicer version compatibility table
    $this->db->query("CREATE TABLE slicerpackages_extensioncompatibility (
      slicerpackages_extension_id bigint NOT NULL,
      slicer_revision character varying(256) NOT NULL
      )");

    // Add release metadata field to packages table
    $this->db->query("ALTER TABLE slicerpackages_package
                      ADD COLUMN release character varying(256) NOT NULL DEFAULT ''");

    // Add index on release column to quickly grab releases
    $this->db->query("CREATE INDEX slicerpackages_package_idx_release
                      ON slicerpackages_package (release)");
    $this->db->query("CREATE INDEX slicerpackages_extension_idx_release
                      ON slicerpackages_extension (release)");
    }

  public function postUpgrade()
    {
    }
}
?>
