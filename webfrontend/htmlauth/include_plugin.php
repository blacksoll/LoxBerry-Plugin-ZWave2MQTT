<?php
require_once "loxberry_web.php";
require_once "loxberry_system.php";
require_once LBPBINDIR . "/defines.php";

class Plugin
{
    static function createHeader($activePage = 1)
    {
        global $navbar;
        global $htmlhead;
        $navbar[1]['Name'] = 'Settings';
        $navbar[1]['URL'] = 'index.php';
        $navbar[1]['active'] = null;

        $navbar[2]['Name'] = 'UI';
        $navbar[2]['URL'] = 'ui.php';
        $navbar[2]['active'] = null;

        $navbar[3]['Name'] = 'Logs';
        $navbar[3]['URL'] = 'log.php';
        $navbar[3]['active'] = null;

        $navbar[$activePage]['active'] = true;
        $htmlhead .= '<link rel="stylesheet" href="css/ui.css">';
        LBWeb::lbheader("ZWave2MQTT Plugin", "https://github.com/zwave-js/zwave-js-ui", "");
    }

    static function h($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    static function boolChecked($value)
    {
        return $value ? 'checked' : '';
    }

    static function loadConfig($file, $default = array())
    {
        if (!file_exists($file)) return $default;
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : $default;
    }

    static function serialCandidates()
    {
        $ports = array();
        foreach (glob('/dev/serial/by-id/*') ?: array() as $item) $ports[] = $item;
        foreach (glob('/dev/ttyACM*') ?: array() as $item) $ports[] = $item;
        foreach (glob('/dev/ttyUSB*') ?: array() as $item) $ports[] = $item;
        $ports = array_values(array_unique($ports));
        sort($ports);
        return $ports;
    }
}
