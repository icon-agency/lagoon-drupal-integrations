<?php

/**
 * @file
 * Drupal all environment configuration file.
 *
 * This file should contain all settings.php configurations that are
 * needed by all environments.
 *
 * Edit as required.
 */

$settings['config_sync_directory'] = '../config/sync';

if (getenv('LAGOON_ENVIRONMENT_TYPE') !== 'production') {
  $settings['skip_permissions_hardening'] = TRUE;
}

// Delete unused files after 12 hours.
$config['system.file']['temporary_maximum_age'] = 43200;
$config['file.settings']['make_unused_managed_files_temporary'] = TRUE;
