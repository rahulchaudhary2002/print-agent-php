# Installation Guide

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- A running instance of the Universal Print Agent, reachable at `http://127.0.0.1:3210` by default

## Install via Composer

```bash
composer require print-agent/print-agent-php
```

Laravel's package auto-discovery registers `PrintAgentServiceProvider` and the `PrintAgent`
facade automatically — no manual registration needed in `config/app.php`.

## Publish the configuration (optional)

```bash
php artisan vendor:publish --tag=print-agent-config
```

This writes `config/print-agent.php` into your app so you can edit defaults directly instead of
(or in addition to) environment variables. Without publishing, the package's own defaults apply.

## Verify it can reach the agent

```bash
php artisan tinker
>>> PrintAgent::testConnection()
=> true
```

If this returns `false`, see [error-handling.md](error-handling.md) — the most common cause is
the agent simply not running (`http://127.0.0.1:3210/docs` should load in a browser if it is).
