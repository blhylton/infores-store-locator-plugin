<?php

/*
Plugin Name: InfoRes Store Locator
Plugin URI: https://github.com/blhylton/infores-store-locator-plugin
Description: WordPress plugin for scraping InfoRes store locations for display
Version: 0.1
Author: Barry Hylton
Author URI: https://github.com/blhylton
License: MIT
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Dependencies not installed');
}

require_once __DIR__ . '/vendor/autoload.php';

