# Flux UI CLI

> [!WARNING] **Deprecated and archived (2026-09-08).** Flux UI now serves its own machine-readable documentation: every page on [fluxui.dev](https://fluxui.dev) supports a `.md` extension (for example `https://fluxui.dev/components/button.md`), plus `llms.txt` and content negotiation. Fetch the docs from the source instead of using this skill.
>
> Announcement: https://x.com/calebporzio/status/2097331733125317092
>
> This repository is archived and no longer maintained.

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

See [skill/SETUP.md](skill/SETUP.md) or run `./skill/install`

## Development

See [src/README.md](src/README.md) for building from source and updating documentation.

## Analytics

Usage data is stored locally in `analytics.jsonl` (no remote telemetry). Analyze with jq:

```bash
FILE=$AGENT_HOME/skills/fluxui-docs/analytics.jsonl

# Command usage counts
cat $FILE | jq -s 'group_by(.command) | map({command: .[0].command, count: length})'

# Most searched terms
cat $FILE | jq -s '[.[] | select(.command=="search")] | group_by(.context.query) | sort_by(-length) | .[0:10]'

# Most viewed docs
cat $FILE | jq -s '[.[] | select(.command=="show" and .context.found)] | group_by(.context.item) | sort_by(-length) | .[0:10]'
```
