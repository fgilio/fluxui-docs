# Analytics

Local usage tracking for development insights. No telemetry.

## Location

`analytics.jsonl` - Created alongside binary after first command run.

## Format

JSONL with one entry per command:
```json
{"command":"show","timestamp":"...","success":true,"exit_code":0,"duration_ms":25,"context":{...}}
```

## When Active

Only when running the built binary. Development runs (`php src/flux`) do not track.

## Clear

```bash
rm analytics.jsonl
```
