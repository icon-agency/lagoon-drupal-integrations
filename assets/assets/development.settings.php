<?php

/**
 * @file
 * Lagoon development environment settings.
 */

// Debugging.
$config['system.logging']['error_level'] = 'verbose';

// Cache killer.
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

// Enforce empty Fastly config.
$config['fastly.settings']['api_key'] = NULL;
$config['fastly.settings']['site_id'] = NULL;
$config['fastly.settings']['service_id'] = NULL;
