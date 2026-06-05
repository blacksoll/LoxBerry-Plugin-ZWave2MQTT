# LoxBerry Plugin ZWave2MQTT

Native LoxBerry plugin that bundles **Z-Wave JS UI** and exposes Z-Wave devices to **LoxBerry** and **Loxone Miniserver** through **MQTT**.

This plugin was built for a setup based on:
- LoxBerry 3.x and 4.x
- Aeotec Z-Stick Gen5 / ZW090-C
- Z-Wave JS UI
- MQTT topics consumed by Loxone via virtual UDP / MQTT commands

Current packaged runtime:
- Node.js 22.22.2
- Z-Wave JS UI 11.19.1

## Features

- Native LoxBerry plugin packaging
- Bundled Node.js runtime
- Embedded `zwave-js-ui`
- MQTT bridge for Loxone Miniserver
- Local persistent store under the LoxBerry plugin directories
- Web UI page inside LoxBerry plus direct Z-Wave JS UI access

## Repository layout

- `plugin.cfg` — LoxBerry plugin metadata
- `bin/` — helper scripts and config generation
- `config/` — default service and secret templates
- `webfrontend/` — LoxBerry plugin pages
- `icons/` — plugin icons
- `sudoers/` — required elevated commands
- `uninstall/` — LoxBerry uninstall hook
- `scripts/make-release.sh` — builds an installable release ZIP from this repo

## Install on LoxBerry

Use a release ZIP from GitHub Releases in **Plugin Management**.

Default first-run values typically used in this project:
- Serial Port: `/dev/serial/by-id/...`
- MQTT Host: `127.0.0.1`
- MQTT Port: `1883`
- MQTT Prefix: `zwave`

## MQTT / Loxone notes

Recommended topic mode for Loxone:
- Topic type: **ValueID topics**
- Payload type: **Just value**

Example Loxone command:

```text
MQTT:\izwave/Bedroom/49/0/Air_temperature=\i\v
```

## Development workflow

1. Edit the plugin source in this repository.
2. Build a release ZIP with `scripts/make-release.sh`.
3. Upload the ZIP to LoxBerry Plugin Management.
4. Tag the repo and attach the ZIP in GitHub Releases.

## Before publishing

Edit the author information in `plugin.cfg`:

```ini
[AUTHOR]
NAME=...
EMAIL=...
WEBSITE=...
```

Also review whether you want to add a project license before making the repository public.

## Tested focus

The current work focused on:
- stable MQTT connectivity to the LoxBerry broker
- correct file permissions for Z-Wave JS UI settings persistence
- Z-Wave JS UI access from the LoxBerry plugin pages
- DietPi / Debian upgrade hardening in `0.1.10`
- LoxBerry 4 metadata alignment and `zwave-js-ui` 11.19.1 packaging in `0.1.11`

## Versioning

See `CHANGELOG.md` for the packaged changes included in the source bundle.

## Compatibility notes

- `0.1.11` updates the compatibility metadata for LoxBerry 4 and adds `WEBSITE` metadata so the newer LoxBerry plugin UI can show the project link.
- LoxBerry 4 introduced MQTT Gateway V2 and changed how retained broker messages are handled on subscribe. This plugin still publishes retained MQTT values, but whether retained messages are forwarded onward depends on the LoxBerry MQTT Gateway configuration and behavior.
