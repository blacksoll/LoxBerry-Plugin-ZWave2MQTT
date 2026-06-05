# Release notes for maintainers

## Create a release ZIP

From the repository root:

```bash
chmod +x scripts/make-release.sh
./scripts/make-release.sh
```

The script writes a ZIP into `release/`.

## Publish to GitHub

1. Commit your changes.
2. Create a tag, for example `v0.1.11`.
3. Push the tag.
4. Open **GitHub Releases**.
5. Create a new release and upload the ZIP from `release/`.

## Important

Before publishing a release, check:
- `plugin.cfg` author fields
- `plugin.cfg` version number
- `README.md`
- whether any local secrets accidentally exist in config files
