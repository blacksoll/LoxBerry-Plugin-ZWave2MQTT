#!/bin/bash
set -e
if systemctl list-unit-files | grep -q '^zwavemqtt\.service'; then
    echo "<INFO> Stopping zwavemqtt service"
    systemctl stop zwavemqtt || true
fi
exit 0
