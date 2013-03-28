<?php

/**
 * Add indices on slicer extenstion table
 */
class Slicerpackages_Upgrade_1_0_6 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  /** Mysql upgrade */
  public function mysql()
    {
    $this->db->query("ALTER TABLE `slicerpackages_extension` ADD INDEX (`slicer_revision`)");
    $this->db->query("ALTER TABLE `slicerpackages_extension` ADD INDEX (`os`)");
    $this->db->query("ALTER TABLE `slicerpackages_extension` ADD INDEX (`arch`)");
    }

  /** Pgsql upgrade */
  public function pgsql()
    {
    $this->db->query("CREATE INDEX slicerpackages_extension_idx_slicer_revision
                      ON slicerpackages_extension (slicer_revision)");
    $this->db->query("CREATE INDEX slicerpackages_extension_idx_os
                      ON slicerpackages_extension (os)");
    $this->db->query("CREATE INDEX slicerpackages_extension_idx_arch
                      ON slicerpackages_extension (arch)");
    }

  public function postUpgrade()
    {
    }
}
?>
