#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

VERSION="$(awk -F= '/^VERSION=/{print $2}' plugin.cfg | tr -d '\r')"
PLUGIN_NAME="$(awk -F= '/^TITLE=/{print $2}' plugin.cfg | tr -d '\r')"
OUTDIR="$ROOT/release"
OUTFILE="$OUTDIR/LoxBerry-Plugin-${PLUGIN_NAME}-${VERSION}.zip"

mkdir -p "$OUTDIR"
rm -f "$OUTFILE"

zip -r "$OUTFILE" . \
  -x '.git/*' \
  -x '.github/*' \
  -x 'release/*' \
  -x '*.zip' \
  -x 'scripts/*' \
  -x '.DS_Store' \
  -x 'Thumbs.db'

echo "Created: $OUTFILE"
