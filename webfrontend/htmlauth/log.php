<?php
require_once 'include_plugin.php';
Plugin::createHeader(3);

$log = shell_exec('sudo /bin/journalctl -u zwavemqtt -n 400 --no-pager 2>&1');
$status = trim(shell_exec('systemctl is-active zwavemqtt 2>/dev/null'));
if ($status === '') $status = 'unknown';

echo '<div class="zw-page">';
echo '<div class="section">';
echo '<h2>Service logs</h2>';
echo '<p>Status: <span class="badge">' . Plugin::h($status) . '</span></p>';
echo '<p class="help">Showing the last 400 lines from journalctl for service <code>zwavemqtt</code>.</p>';
echo '<pre class="zw-log">' . Plugin::h($log) . '</pre>';
echo '</div>';
echo '</div>';
LBWeb::lbfooter();
