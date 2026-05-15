<?php
$configfile = LBPCONFIGDIR . "/service.json";
$secretfile = LBPCONFIGDIR . "/secrets.json";
$settingsfile = LBPDATADIR . "/store/settings.json";
$envfile = LBPCONFIGDIR . "/zwavemqtt.env";
$logunit = "zwavemqtt";

$L = LBSystem::readlanguage("language.ini");
$navbar = array();
$htmlhead = '';
