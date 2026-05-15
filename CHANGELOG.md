# Changelog

## 0.1.10
- Pin bundled Node.js to 22.22.2
- Wait for `network-online.target`
- Add `dialout` supplementary group for serial access
- Improve download fallback during install
- Keep `zwave-js-ui` pinned to 11.14.0

## 0.1.9
- Fix direct UI link to force `http://` for the embedded Z-Wave JS UI page

## 0.1.8
- Improve Logs page layout and sizing

## 0.1.7
- Tidy plugin page CSS and reduce oversized layout

## 0.1.6
- Fix MQTT credential persistence
- Fix file ownership and write permissions for the settings store

## 0.1.5
- Additional permission fixes for saving settings from Z-Wave JS UI

## 0.1.4
- Reduce noisy install notices
- Safer LoxBerry MQTT fallback handling

## 0.1.3
- Fix MQTT authentication fields being omitted from the generated settings

## 0.1.2
- Improve MQTT credential handling and allow manual override

## 0.1.1
- Add MQTT host fallback to `127.0.0.1:1883`

## 0.1.0
- Initial installable LoxBerry wrapper around Z-Wave JS UI
