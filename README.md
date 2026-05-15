# LoxBerry Plugin ZWave2MQTT - Developed with the help of Open Ai for pesronal use but you are welcome to use it. Don't ask for more, cause i'm not a developer !!!

Native LoxBerry plugin that bundles **Z-Wave JS UI** and exposes Z-Wave devices to **LoxBerry** and **Loxone Miniserver** through **MQTT**.

This plugin was built for a setup based on:
- LoxBerry 3.x
- Aeotec Z-Stick Gen5 / ZW090-C
- Z-Wave JS UI
- Many thanks to the  Z-Wave JS UI Team :
@AlCalzone · Shaper of Waves, Reader of Specifications, Teacher of Bots, Broker of IOs, Crazy enough to start all of this
@blhoward2 · Writer of Manifests, Master of Consistency
@marcus-j-davies · Browser of Configs, Plumber of Red Nodes
@robertsLando · Discoverer of Greatness, Builder of Frontends, Stacker of Statistics
- MQTT topics consumed by Loxone via virtual UDP / MQTT commands
- Many thanks to the Loxberry Team for giving us this excellent tool which drives Loxone "BEYOND THE LIMITS"

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


## Tested focus

The current work focused on:
- stable MQTT connectivity to the LoxBerry broker
- correct file permissions for Z-Wave JS UI settings persistence
- Z-Wave JS UI access from the LoxBerry plugin pages
- DietPi / Debian upgrade hardening in `0.1.10`

## Versioning

See `CHANGELOG.md` for the packaged changes included in the source bundle.
