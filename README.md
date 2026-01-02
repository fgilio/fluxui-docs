# Flux UI CLI

> **Note**: This tool caches documentation from [fluxui.dev](https://fluxui.dev). Permission to redistribute this content is pending approval from the Flux UI team.

Self-contained CLI for accessing Flux UI documentation offline. No PHP required.

## Usage

```bash
# List all documentation
./fluxui-docs docs

# Search for components
./fluxui-docs search button

# Show component documentation
./fluxui-docs show modal
```

## Install

See [SETUP.md](SETUP.md) or run `./install`

## Development

See [src/README.md](src/README.md) for building from source and updating documentation.

## Analytics

Usage data is stored locally in `analytics.jsonl` (no remote telemetry). Analyze with jq:

```bash
FILE=~/.claude/skills/fluxui-docs/analytics.jsonl

# Command usage counts
cat $FILE | jq -s 'group_by(.command) | map({command: .[0].command, count: length})'

# Most searched terms
cat $FILE | jq -s '[.[] | select(.command=="search")] | group_by(.context.query) | sort_by(-length) | .[0:10]'

# Most viewed docs
cat $FILE | jq -s '[.[] | select(.command=="show" and .context.found)] | group_by(.context.item) | sort_by(-length) | .[0:10]'
```
