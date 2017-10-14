<?php

/**
 *
 * FUNCTIONS THAT CAN BE USED BY THEMES/PLUGINS DEVELOPERS
 *
 */

// Rename the media automatically based on the settings
function mfrh_rename( $mediaId ) {
  global $mfrh_core;
  return $mfrh_core->rename( $mediaId );
}

?>
