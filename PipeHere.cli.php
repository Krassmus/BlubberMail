#!/usr/bin/php -q
<?php

require_once dirname(__file__)."/../../../../cli/studip_cli_env.inc.php";
require_once dirname(__file__)."/models/MailProcessor.class.php";

PluginManager::getInstance()->getPlugins("SystemPlugin");

if (!$GLOBALS['ABSOLUTE_URI_STUDIP']) {
    echo 'You MUST set correct values for $ABSOLUTE_URI_STUDIP in config_local.inc.php!';
    exit(69);
}

//check if plugin is enabled
$blubberMailEnabled = false;
$plugin_infos = PluginManager::getInstance()->getPluginInfos("SystemPlugin");
foreach ($plugin_infos as $plugin_info) {
    if ((strtolower($plugin_info['class']) === "blubbermail") && $plugin_info['enabled']) {
        $blubberMailEnabled = true;
    }
}
if (!$blubberMailEnabled) {
    echo "BlubberMail-Plugin ist nicht aktiviert. Nachrichten werden nicht als Blubber weitergestellt.";
    exit(69);
}

$rawmail = file_get_contents('php://stdin');
try {
    MailProcessor::getInstance()->processBlubberMail($rawmail);
} catch(Exception $e) {
    echo $e->getMessage();
    exit(69);
}