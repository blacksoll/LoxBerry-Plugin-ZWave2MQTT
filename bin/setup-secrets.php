<?php
error_reporting(E_ERROR | E_PARSE);
ini_set("display_errors", "0");
require_once "loxberry_system.php";
require_once LBPBINDIR . "/defines.php";

function random_hex($bytes = 16) {
    return bin2hex(random_bytes($bytes));
}

$secrets = array(
    "sessionSecret" => random_hex(32),
    "securityKeys" => array(
        "S0_Legacy" => random_hex(16),
        "S2_Unauthenticated" => random_hex(16),
        "S2_Authenticated" => random_hex(16),
        "S2_AccessControl" => random_hex(16)
    )
);

if (file_exists($secretfile)) {
    $existing = json_decode(file_get_contents($secretfile), true);
    if (is_array($existing)) {
        if (!empty($existing["sessionSecret"])) {
            $secrets["sessionSecret"] = $existing["sessionSecret"];
        }
        if (!empty($existing["securityKeys"]) && is_array($existing["securityKeys"])) {
            foreach ($existing["securityKeys"] as $k => $v) {
                if (!empty($v)) {
                    $secrets["securityKeys"][$k] = $v;
                }
            }
        }
    }
}

file_put_contents($secretfile, json_encode($secrets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
@chmod($secretfile, 0640);
@chown($secretfile, "root");
@chgrp($secretfile, "root");
