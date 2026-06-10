<?php
// Load WordPress environment
require_once('../../../wp-load.php');

// Initialize PUC manually for testing
require get_template_directory() . '/inc/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/rameshkhadka61/liveradiostream/',
	get_template_directory() . '/functions.php',
	'liveradiostream-theme'
);

$myUpdateChecker->setBranch('main');

// Force a check
$update = $myUpdateChecker->requestUpdate();

if ($update) {
    echo "Update found!\n";
    print_r($update);
} else {
    echo "No update found.\n";
}
