<?php
require_once 'include_plugin.php';

Plugin::createHeader(1);

$config = Plugin::loadConfig($configfile, array());
$message = "";
$error = "";

function to_bool($name) {
    return isset($_POST[$name]) ? true : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['zwavePort'] = trim($_POST['zwavePort'] ?? '/dev/ttyACM0');
    $config['uiPort'] = intval($_POST['uiPort'] ?? 8091);
    if ($config['uiPort'] < 1 || $config['uiPort'] > 65535) $config['uiPort'] = 8091;
    $config['mqttPrefix'] = trim($_POST['mqttPrefix'] ?? 'zwave');
    if ($config['mqttPrefix'] === '') $config['mqttPrefix'] = 'zwave';
    $config['useMqttGateway'] = to_bool('useMqttGateway');
    $config['mqttHost'] = trim($_POST['mqttHost'] ?? '127.0.0.1');
    $config['mqttPort'] = intval($_POST['mqttPort'] ?? 1883);
    if ($config['mqttPort'] < 1 || $config['mqttPort'] > 65535) $config['mqttPort'] = 1883;
    $config['mqttUser'] = trim($_POST['mqttUser'] ?? '');
    $postedPassword = isset($_POST['mqttPassword']) ? trim($_POST['mqttPassword']) : null;
    if ($postedPassword !== null && $postedPassword !== '') {
        $config['mqttPassword'] = $postedPassword;
    } else if (!isset($config['mqttPassword'])) {
        $config['mqttPassword'] = '';
    }
    $config['retain'] = to_bool('retain');
    $config['gatewayType'] = intval($_POST['gatewayType'] ?? 0);
    if (!in_array($config['gatewayType'], array(0,1,2), true)) $config['gatewayType'] = 0;
    $config['payloadType'] = intval($_POST['payloadType'] ?? 2);
    if (!in_array($config['payloadType'], array(0,1,2), true)) $config['payloadType'] = 2;
    $config['nodeNames'] = to_bool('nodeNames');
    $config['includeNodeInfo'] = to_bool('includeNodeInfo');
    $config['uiLogEnabled'] = to_bool('uiLogEnabled');
    $config['zwaveLogEnabled'] = to_bool('zwaveLogEnabled');
    $config['uiLogLevel'] = trim($_POST['uiLogLevel'] ?? 'info');
    $config['zwaveLogLevel'] = trim($_POST['zwaveLogLevel'] ?? 'info');

    file_put_contents($configfile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    shell_exec('php ' . escapeshellarg(LBPBINDIR . '/setup-secrets.php'));
    shell_exec('php ' . escapeshellarg(LBPBINDIR . '/update-config.php'));

    $action = $_POST['formaction'] ?? 'save';
    if ($action === 'restart') {
        shell_exec('sudo /bin/systemctl restart zwavemqtt 2>&1');
        $message = "Settings saved and service restarted.";
    } else if ($action === 'start') {
        shell_exec('sudo /bin/systemctl start zwavemqtt 2>&1');
        $message = "Settings saved and service started.";
    } else if ($action === 'stop') {
        shell_exec('sudo /bin/systemctl stop zwavemqtt 2>&1');
        $message = "Settings saved and service stopped.";
    } else {
        $message = "Settings saved.";
    }
}

$config = Plugin::loadConfig($configfile, array());
$status = trim(shell_exec('systemctl is-active zwavemqtt 2>/dev/null'));
if ($status === '') $status = 'unknown';
$enabled = trim(shell_exec('systemctl is-enabled zwavemqtt 2>/dev/null'));
if ($enabled === '') $enabled = 'unknown';
$ports = Plugin::serialCandidates();

echo '<div class="zw-page">';
if ($message) echo '<div class="zw-alert zw-alert-success">' . Plugin::h($message) . '</div>'; 
if ($error) echo '<div class="zw-alert zw-alert-error">' . Plugin::h($error) . '</div>'; 

echo '<div class="wide">';
echo '<div class="section">';
echo '<h2>Service status</h2>';
echo '<p>Active: <span class="badge">' . Plugin::h($status) . '</span> &nbsp; Enabled: <span class="badge">' . Plugin::h($enabled) . '</span></p>';
echo '<p class="help">After the first install, open the UI and verify the detected controller, keys, and MQTT topics.</p>';
echo '<p><a class="btn" href="ui.php">Open Z-Wave JS UI</a> <a class="btn" href="log.php">Open logs</a></p>';
echo '</div>';

echo '<form method="post">';
echo '<div class="section">';
echo '<h2>Z-Wave</h2>';
echo '<label for="zwavePort">Serial port</label>';
echo '<input id="zwavePort" name="zwavePort" type="text" list="serialports" value="' . Plugin::h($config['zwavePort'] ?? '/dev/ttyACM0') . '">';
echo '<datalist id="serialports">';
foreach ($ports as $port) echo '<option value="' . Plugin::h($port) . '">';
echo '</datalist>';
echo '<div class="help">Preferred: persistent path under <code>/dev/serial/by-id/...</code>.</div>';

echo '<label for="uiPort">UI port</label>';
echo '<input id="uiPort" name="uiPort" type="number" min="1" max="65535" value="' . Plugin::h($config['uiPort'] ?? 8091) . '">';

echo '<div class="inline">';
echo '<label><input type="checkbox" name="zwaveLogEnabled" ' . Plugin::boolChecked($config['zwaveLogEnabled'] ?? true) . '> Enable Z-Wave logging</label>';
echo '</div>';

echo '<label for="zwaveLogLevel">Z-Wave log level</label>';
echo '<select id="zwaveLogLevel" name="zwaveLogLevel">';
foreach (array('error','warn','info','debug','silly') as $lvl) {
    $sel = (($config['zwaveLogLevel'] ?? 'info') === $lvl) ? 'selected' : '';
    echo '<option value="' . Plugin::h($lvl) . '" ' . $sel . '>' . Plugin::h($lvl) . '</option>';
}
echo '</select>';
echo '</div>';

echo '<div class="section">';
echo '<h2>MQTT</h2>';
echo '<p class="help">Recommended for first setup on LoxBerry: leave <strong>Use LoxBerry MQTT core</strong> off and enter 127.0.0.1, port 1883, plus your MQTT username/password manually.</p>';
echo '<div class="inline">';
echo '<label><input type="checkbox" name="useMqttGateway" ' . Plugin::boolChecked($config['useMqttGateway'] ?? false) . '> Use LoxBerry MQTT core</label>';
echo '</div>';
echo '<label for="mqttPrefix">MQTT topic prefix</label>';
echo '<input id="mqttPrefix" name="mqttPrefix" type="text" value="' . Plugin::h($config['mqttPrefix'] ?? 'zwave') . '">';
echo '<label for="mqttHost">Custom broker host</label>';
echo '<input id="mqttHost" name="mqttHost" type="text" value="' . Plugin::h($config['mqttHost'] ?? '127.0.0.1') . '">';
echo '<label for="mqttPort">Custom broker port</label>';
echo '<input id="mqttPort" name="mqttPort" type="number" min="1" max="65535" value="' . Plugin::h($config['mqttPort'] ?? 1883) . '">';
echo '<label for="mqttUser">Custom broker username</label>';
echo '<input id="mqttUser" name="mqttUser" type="text" value="' . Plugin::h($config['mqttUser'] ?? '') . '">';
echo '<label for="mqttPassword">Custom broker password</label>';
echo '<input id="mqttPassword" name="mqttPassword" type="password" value="' . Plugin::h($config['mqttPassword'] ?? '') . '">';
echo '<div class="help">If MQTT auth is enabled on your broker, enter username and password here. If you leave the password unchanged, the saved password is kept.</div>';
echo '<div class="inline">';
echo '<label><input type="checkbox" name="retain" ' . Plugin::boolChecked($config['retain'] ?? true) . '> Retain MQTT values</label>';
echo '</div>';
echo '</div>';

echo '<div class="section">';
echo '<h2>Gateway topic style</h2>';
echo '<label for="gatewayType">Topic type</label>';
echo '<select id="gatewayType" name="gatewayType">';
$gatewayOptions = array(0 => 'ValueID topics', 1 => 'Named topics', 2 => 'Manual');
foreach ($gatewayOptions as $v => $label) {
    $sel = (intval($config['gatewayType'] ?? 0) === $v) ? 'selected' : '';
    echo '<option value="' . $v . '" ' . $sel . '>' . Plugin::h($label) . '</option>';
}
echo '</select>';

echo '<label for="payloadType">Payload type</label>';
echo '<select id="payloadType" name="payloadType">';
$payloadOptions = array(0 => 'Time + value object', 1 => 'Full ValueID object', 2 => 'Just value');
foreach ($payloadOptions as $v => $label) {
    $sel = (intval($config['payloadType'] ?? 2) === $v) ? 'selected' : '';
    echo '<option value="' . $v . '" ' . $sel . '>' . Plugin::h($label) . '</option>';
}
echo '</select>';

echo '<div class="inline">';
echo '<label><input type="checkbox" name="nodeNames" ' . Plugin::boolChecked($config['nodeNames'] ?? false) . '> Use node names instead of numeric node IDs</label>';
echo '</div>';
echo '<div class="inline">';
echo '<label><input type="checkbox" name="includeNodeInfo" ' . Plugin::boolChecked($config['includeNodeInfo'] ?? false) . '> Include node info in MQTT messages</label>';
echo '</div>';

echo '<div class="inline">';
echo '<label><input type="checkbox" name="uiLogEnabled" ' . Plugin::boolChecked($config['uiLogEnabled'] ?? true) . '> Enable UI/gateway logging</label>';
echo '</div>';
echo '<label for="uiLogLevel">UI/gateway log level</label>';
echo '<select id="uiLogLevel" name="uiLogLevel">';
foreach (array('error','warn','info','debug','silly') as $lvl) {
    $sel = (($config['uiLogLevel'] ?? 'info') === $lvl) ? 'selected' : '';
    echo '<option value="' . Plugin::h($lvl) . '" ' . $sel . '>' . Plugin::h($lvl) . '</option>';
}
echo '</select>';

echo '<p class="help">For Loxone, the easiest starting point is usually <strong>ValueID topics</strong> + <strong>Just value</strong>.</p>';
echo '</div>';

echo '<div class="section actions">';
echo '<button class="btn" name="formaction" value="save" type="submit">Save</button>';
echo '<button class="btn" name="formaction" value="restart" type="submit">Save & Restart</button>';
echo '<button class="btn" name="formaction" value="start" type="submit">Save & Start</button>';
echo '<button class="btn" name="formaction" value="stop" type="submit">Save & Stop</button>';
echo '</div>';
echo '</form>';

echo '</div>';
LBWeb::lbfooter();
