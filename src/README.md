# flux-ui - Development

## Setup

```bash
cd ~/.claude/skills/flux-ui/src
composer install
./flux-ui --help
```

## Building

First-time setup (builds PHP + micro.sfx):
```bash
phpcli-spc-setup --doctor
phpcli-spc-build
```

Build and install to skill root:
```bash
./flux-ui build              # builds + copies to ../flux-ui
./flux-ui build --no-install # only builds to builds/flux-ui
```

## Testing

```bash
./vendor/bin/pest
```

## License

MIT
