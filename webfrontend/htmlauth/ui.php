<?php
require_once 'include_plugin.php';
Plugin::createHeader(2);

$cfg = Plugin::loadConfig($configfile, array());
$port = intval($cfg['uiPort'] ?? 8091);
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$host = preg_replace('/:\d+$/', '', $host);
$url = 'http://' . $host . ':' . $port;

echo '<div class="lb-container zwui-compact">';
echo '<h2>Z-Wave JS UI</h2>';
echo '<p>The Z-Wave JS UI runs on plain HTTP on port ' . Plugin::h($port) . '.</p>';
echo '<p>Open it with this URL:</p>';
echo '<div class="codebox"><code>' . Plugin::h($url) . '</code></div>';
echo '<p><a class="btn" href="' . Plugin::h($url) . '" id="openui" target="_blank" rel="noopener noreferrer">Open UI</a></p>';
echo '<p class="hint">If your browser upgrades to HTTPS automatically, copy the URL above and make sure it starts with <strong>http://</strong>.</p>';
echo '</div>';
LBWeb::lbfooter();
