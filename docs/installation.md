# Installation Guide

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- A running instance of the Universal Print Agent, reachable at `http://127.0.0.1:3210` by default

## Install via Composer

This package is **not published on Packagist** (the GitHub repository is private). Install it
directly from the git repository instead: add a `repositories` entry to the *consuming* Laravel
app's `composer.json` pointing at the repo, then require it as normal.

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:rahulchaudhary2002/print-agent-php.git"
        }
    ]
}
```

```bash
composer require print-agent/print-agent-php:^1.0
```

Composer clones the repo via git (using whatever SSH key/deploy key has access to it) instead of
looking it up on Packagist. This works for a private repo — anyone installing it needs their own
SSH access to `rahulchaudhary2002/print-agent-php`, the same way you'd grant a collaborator
access to any other private repo.

If this package is ever made public and submitted to Packagist, the `repositories` block above
becomes unnecessary and a plain `composer require print-agent/print-agent-php` will work instead.

Laravel's package auto-discovery registers `PrintAgentServiceProvider` and the `PrintAgent`
facade automatically either way — no manual registration needed in `config/app.php`.

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
