#!/bin/bash
set -e

COMMAND=$0
PTEMPDIR=$1
PSHNAME=$2
PDIR=$3
PVERSION=$4
PTEMPPATH=$6

PCGI=$LBPCGI/$PDIR
PHTML=$LBPHTML/$PDIR
PTEMPL=$LBPTEMPL/$PDIR
PDATA=$LBPDATA/$PDIR
PLOG=$LBPLOG/$PDIR
PCONFIG=$LBPCONFIG/$PDIR
PSBIN=$LBPSBIN/$PDIR
PBIN=$LBPBIN/$PDIR

NODE_VERSION="22.22.2"

require_cmd() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "<FAIL> Required command not found: $1"
        exit 2
    fi
}

download_file() {
    local url="$1"
    local output="$2"
    if command -v curl >/dev/null 2>&1; then
        curl -fsSL "$url" -o "$output"
    elif command -v wget >/dev/null 2>&1; then
        wget -q -O "$output" "$url"
    else
        echo "<FAIL> Neither curl nor wget is installed"
        exit 2
    fi
}

echo "<INFO> Installing ZWave2MQTT runtime into /opt/zwave-js-ui"

require_cmd tar
require_cmd xz

mkdir -p /opt/zwave-js-ui
rm -rf /opt/zwave-js-ui/node /opt/zwave-js-ui/app

ARCH=$(uname -m)
case $ARCH in
  x86_64) NODE_ARCH="x64" ;;
  aarch64) NODE_ARCH="arm64" ;;
  armv7l|armv6l) NODE_ARCH="armv7l" ;;
  *)
    echo "<FAIL> Unsupported architecture: $ARCH"
    exit 2
    ;;
esac

NODE_BASE_URL="https://nodejs.org/download/release/v${NODE_VERSION}"
NODE_TARBALL="node-v${NODE_VERSION}-linux-${NODE_ARCH}.tar.xz"
NODE_URL="${NODE_BASE_URL}/${NODE_TARBALL}"

echo "<INFO> Downloading ${NODE_TARBALL}"
cd /opt/zwave-js-ui
download_file "$NODE_URL" "$NODE_TARBALL"
tar -xf "$NODE_TARBALL"
NODE_DIR="${NODE_TARBALL%.tar.xz}"
mv "$NODE_DIR" node
rm -f "$NODE_TARBALL"

export PATH=/opt/zwave-js-ui/node/bin:$PATH

echo "<INFO> Installing zwave-js-ui from npm"
mkdir -p /opt/zwave-js-ui/app
npm install -g --omit=dev --unsafe-perm --prefix /opt/zwave-js-ui/app zwave-js-ui@11.19.1

echo "<INFO> Preparing data folders"
mkdir -p "$PDATA/store" "$PDATA/store/config"
chown -R loxberry:loxberry "$PDATA"

echo "<INFO> Preparing secrets and config"
php -d display_errors=0 "$PBIN/setup-secrets.php" >/dev/null 2>&1 || true
php -d display_errors=0 "$PBIN/update-config.php" >/dev/null 2>&1 || true
[ -f "$PDATA/store/settings.json" ] || php -d display_errors=0 "$PBIN/update-config.php"
chown -R loxberry:loxberry "$PDATA"
find "$PDATA" -type d -exec chmod 775 {} \;
find "$PDATA" -type f -exec chmod 664 {} \;
chown -R loxberry:loxberry /opt/zwave-js-ui

echo "<INFO> Installing systemd unit"
sed "s|__ENVFILE__|$PCONFIG/zwavemqtt.env|g" "$PCONFIG/zwavemqtt.service" > /etc/systemd/system/zwavemqtt.service

if getent group dialout >/dev/null 2>&1; then
    echo "<INFO> Service will run with supplementary group dialout"
else
    echo "<WARNING> Group dialout not found. Serial port access may fail until the system group exists."
fi

systemctl daemon-reload
systemctl enable zwavemqtt
systemctl restart zwavemqtt

echo "<OK> Installation finished"
exit 0
