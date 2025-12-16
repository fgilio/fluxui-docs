# Flux UI CLI

Self-contained CLI for accessing Flux UI documentation offline. No PHP required.

## Usage

```bash
# List all documentation
./flux docs

# Search for components
./flux search button

# Show component documentation
./flux show modal

# Update docs from fluxui.dev
./flux update
```

## Installation

The `flux` binary is self-contained. You can:

1. Run directly: `~/.claude/skills/flux-ui/flux docs`
2. Symlink to PATH: `ln -sf ~/.claude/skills/flux-ui/flux ~/.local/bin/flux`

## Updating Documentation

When Flux UI releases new components:

```bash
./flux update
git add data/
git commit -m "Update Flux UI docs"
git push
```

## Development

See [src/README.md](src/README.md) for building from source.

### Quick rebuild

```bash
cd src
php flux build
```

### Full rebuild (including PHP runtime)

```bash
cd src
phpcli-spc-setup
./spc doctor --auto-fix
phpcli-spc-build --extensions "bcmath,ctype,curl,dom,fileinfo,filter,iconv,mbstring,mbregex,openssl,pdo,phar,pcntl,posix,session,simplexml,sockets,sodium,tokenizer,xml,zlib"
php flux build
```
