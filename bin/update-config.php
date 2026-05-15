<?php
error_reporting(E_ERROR | E_PARSE);
ini_set("display_errors", "0");
require_once "loxberry_system.php";
require_once "loxberry_io.php";
require_once LBPBINDIR . "/defines.php";

function boolval_safe($value, $default = false) {
    if (is_bool($value)) return $value;
    if ($value === null) return $default;
    if (is_string($value)) {
        $v = strtolower(trim($value));
        return in_array($v, array("1","true","on","yes"), true);
    }
    return (bool)$value;
}

function load_json_file($file, $default) {
    if (!file_exists($file)) return $default;
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : $default;
}

$cfg = load_json_file($configfile, array());
$sec = load_json_file($secretfile, array());

$storeDir = LBPDATADIR . "/store";
$configDir = $storeDir . "/config";
if (!is_dir($storeDir)) @mkdir($storeDir, 0775, true);
if (!is_dir($configDir)) @mkdir($configDir, 0775, true);

$settings = load_json_file($settingsfile, array());

$settings["zwave"] = isset($settings["zwave"]) && is_array($settings["zwave"]) ? $settings["zwave"] : array();
$settings["mqtt"] = isset($settings["mqtt"]) && is_array($settings["mqtt"]) ? $settings["mqtt"] : array();
$settings["gateway"] = isset($settings["gateway"]) && is_array($settings["gateway"]) ? $settings["gateway"] : array();
$settings["backup"] = isset($settings["backup"]) && is_array($settings["backup"]) ? $settings["backup"] : array();
$settings["ui"] = isset($settings["ui"]) && is_array($settings["ui"]) ? $settings["ui"] : array();
$settings["zniffer"] = isset($settings["zniffer"]) && is_array($settings["zniffer"]) ? $settings["zniffer"] : array();

$manualMqttHost = trim($cfg["mqttHost"] ?? "127.0.0.1");
$manualMqttPort = intval($cfg["mqttPort"] ?? 1883);
$manualMqttUser = trim($cfg["mqttUser"] ?? "");
$manualMqttPass = strval($cfg["mqttPassword"] ?? "");
$manualMqttAuth = (!empty($manualMqttUser) || !empty($manualMqttPass));

function recursive_fix_permissions($path, $user = 'loxberry', $group = 'loxberry') {
    if (!file_exists($path)) return;
    @chown($path, $user);
    @chgrp($path, $group);
    @chmod($path, is_dir($path) ? 0775 : 0664);
    if (is_dir($path)) {
        $items = scandir($path);
        if (is_array($items)) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                recursive_fix_permissions($path . '/' . $item, $user, $group);
            }
        }
    }
}

$useCoreMqtt = boolval_safe($cfg["useMqttGateway"] ?? false);
$mqttHost = $manualMqttHost;
$mqttPort = $manualMqttPort;
$mqttUser = $manualMqttUser;
$mqttPass = $manualMqttPass;
$mqttAuth = $manualMqttAuth;

if ($useCoreMqtt && function_exists('mqtt_connectiondetails')) {
    $creds = @mqtt_connectiondetails();
    if (!is_array($creds)) $creds = array();

    $coreHost = trim(strval($creds["brokerhost"] ?? ""));
    $corePort = intval($creds["brokerport"] ?? 0);
    $coreUser = trim(strval($creds["brokeruser"] ?? ""));
    $corePass = strval($creds["brokerpass"] ?? "");
    $coreAuth = (!empty($coreUser) || !empty($corePass));

    if ($coreHost !== "") $mqttHost = $coreHost;
    if ($corePort >= 1 && $corePort <= 65535) $mqttPort = $corePort;
    if ($coreAuth) {
        $mqttUser = $coreUser;
        $mqttPass = $corePass;
        $mqttAuth = true;
    }
}

if ($mqttHost === "") $mqttHost = "127.0.0.1";
if ($mqttPort < 1 || $mqttPort > 65535) $mqttPort = 1883;

$zwavePort = trim($cfg["zwavePort"] ?? "");
if ($zwavePort === "") $zwavePort = "/dev/ttyACM0";

$settings["zwave"] = array_merge($settings["zwave"], array(
    "enabled" => true,
    "port" => $zwavePort,
    "allowBootloaderOnly" => false,
    "commandsTimeout" => 30,
    "logLevel" => $cfg["zwaveLogLevel"] ?? "info",
    "logEnabled" => boolval_safe($cfg["zwaveLogEnabled"] ?? true),
    "securityKeys" => $sec["securityKeys"] ?? array(),
    "securityKeysLongRange" => isset($settings["zwave"]["securityKeysLongRange"]) && is_array($settings["zwave"]["securityKeysLongRange"]) ? $settings["zwave"]["securityKeysLongRange"] : new stdClass(),
    "deviceConfigPriorityDir" => $configDir,
    "logToFile" => true,
    "maxFiles" => 7,
    "serverEnabled" => false,
    "serverServiceDiscoveryDisabled" => true,
    "enableSoftReset" => true,
    "enableStatistics" => true,
    "serverPort" => 3000,
    "maxNodeEventsQueueSize" => 100,
    "higherReportsTimeout" => false,
    "disableControllerRecovery" => false,
    "disclaimerVersion" => 1
));

$settings["mqtt"] = array_merge($settings["mqtt"], array(
    "name" => "zwavemqtt",
    "host" => $mqttHost,
    "port" => $mqttPort,
    "qos" => 1,
    "prefix" => trim($cfg["mqttPrefix"] ?? "zwave"),
    "reconnectPeriod" => 3000,
    "retain" => boolval_safe($cfg["retain"] ?? true),
    "clean" => true,
    "auth" => $mqttAuth,
    "username" => $mqttUser,
    "password" => $mqttPass,
    "_ca" => "",
    "ca" => "",
    "_cert" => "",
    "cert" => "",
    "_key" => "",
    "key" => "",
    "disabled" => false
));

$settings["gateway"] = array_merge($settings["gateway"], array(
    "type" => intval($cfg["gatewayType"] ?? 0),
    "plugins" => array(),
    "authEnabled" => false,
    "payloadType" => intval($cfg["payloadType"] ?? 2),
    "nodeNames" => boolval_safe($cfg["nodeNames"] ?? false),
    "hassDiscovery" => false,
    "discoveryPrefix" => "homeassistant",
    "logEnabled" => boolval_safe($cfg["uiLogEnabled"] ?? true),
    "logLevel" => $cfg["uiLogLevel"] ?? "info",
    "logToFile" => true,
    "values" => isset($settings["gateway"]["values"]) && is_array($settings["gateway"]["values"]) ? $settings["gateway"]["values"] : array(),
    "jobs" => isset($settings["gateway"]["jobs"]) && is_array($settings["gateway"]["jobs"]) ? $settings["gateway"]["jobs"] : array(),
    "includeNodeInfo" => boolval_safe($cfg["includeNodeInfo"] ?? false),
    "disableChangelog" => false
));

$settings["backup"] = array_merge($settings["backup"], array(
    "storeBackup" => true,
    "storeCron" => "0 3 * * *",
    "storeKeep" => 7,
    "nvmBackup" => false,
    "nvmBackupOnEvent" => false,
    "nvmCron" => "10 3 * * *",
    "nvmKeep" => 7
));

$settings["ui"] = array_merge($settings["ui"], array(
    "darkMode" => false,
    "navTabs" => true,
    "compactMode" => false
));

$settings["zniffer"] = array_merge($settings["zniffer"], array(
    "enabled" => false,
    "port" => "",
    "logEnabled" => true,
    "logToFile" => true,
    "maxFiles" => 7,
    "securityKeys" => array(
        "S2_Unauthenticated" => "",
        "S2_Authenticated" => "",
        "S2_AccessControl" => "",
        "S0_Legacy" => ""
    ),
    "securityKeysLongRange" => array(
        "S2_Authenticated" => "",
        "S2_AccessControl" => ""
    ),
    "convertRSSI" => false
));

file_put_contents($settingsfile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
recursive_fix_permissions(LBPDATADIR);
recursive_fix_permissions($storeDir);
recursive_fix_permissions($configDir);
recursive_fix_permissions($settingsfile);

$env = array(
    'STORE_DIR=' . $storeDir,
    'PORT=' . intval($cfg["uiPort"] ?? 8091),
    'SESSION_SECRET=' . ($sec["sessionSecret"] ?? ''),
    'DEFAULT_USERNAME=admin',
    'DEFAULT_PASSWORD=zwave',
    'HOST=0.0.0.0'
);
file_put_contents($envfile, implode(PHP_EOL, $env) . PHP_EOL);
@chmod($envfile, 0640);
@chown($envfile, "root");
@chgrp($envfile, "root");
