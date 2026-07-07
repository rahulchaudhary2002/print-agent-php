# Configuration Guide

All configuration lives in `config/print-agent.php`, and every key has an environment-variable
override so you don't need to publish the file just to change a value.

| Config key | Env variable | Default | Notes |
|---|---|---|---|
| `base_url` | `PRINT_AGENT_BASE_URL` | `http://127.0.0.1:3210/api/v1` | Change only if the agent runs on a non-default port or you've enabled `allowRemote` on it |
| `timeout` | `PRINT_AGENT_TIMEOUT` | `10` (seconds) | Applies per request |
| `api_token` | `PRINT_AGENT_API_TOKEN` | `null` | Only needed if the agent's `requireApiKey` is enabled — format is `"{apiKey}:{apiSecret}"` |
| `retry.times` | `PRINT_AGENT_RETRY_TIMES` | `2` | Connection-level retries only — a 4xx/5xx response is never retried |
| `retry.sleep` | `PRINT_AGENT_RETRY_SLEEP_MS` | `100` | Milliseconds between retries |
| `verify_ssl` | `PRINT_AGENT_VERIFY_SSL` | `true` | Irrelevant unless you've put TLS in front of the agent |
| `application_name` | `PRINT_AGENT_APPLICATION_NAME` | your `APP_NAME` | Sent as an informational header |
| `application_version` | `PRINT_AGENT_APPLICATION_VERSION` | `1.0.0` | Sent as an informational header |
| `routes.enabled` | `PRINT_AGENT_ROUTES_ENABLED` | `false` | See below |

## Getting an API token

Only relevant if you've turned on `requireApiKey` in the agent's own configuration
(`PUT /api/v1/config`). Register an application directly against the agent:

```bash
curl -X POST http://127.0.0.1:3210/api/v1/applications \
  -H 'content-type: application/json' \
  -d '{"name": "My Laravel App"}'
```

The response includes `apiKey` and `apiSecret` (the secret is shown once). Set:

```env
PRINT_AGENT_API_TOKEN="pk_xxxxx:sk_xxxxx"
```

## Optional routes

Setting `PRINT_AGENT_ROUTES_ENABLED=true` registers four thin proxy routes under your own app
(`/print-agent/health`, `/print-agent/status`, `/print-agent/printers`, `/print-agent/queue`) —
useful for wiring the agent's status into your own monitoring without writing a controller.
Change the prefix and middleware via `routes.prefix`/`routes.middleware` in the config file.
