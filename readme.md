# xpb
xpb is a dead simple pastebin and URL shortener, built with [Lumen](https://lumen.laravel.com/).

> This is still very much a work in progress, use at own risk.

## Features
* Redis database
* Deletes pastes not accessed in 90 days
* Syntax highlighting with overrideable language

## Requirements
* nginx (not tested with Apache)
* Redis-server
* Everything else that [Lumen](https://lumen.laravel.com/) needs

## Install
TBD

## Add paste
If paste is a URL; xpb will redirect instead of showing the content — acting as a URL shortener.

### Alias
Put this in your `.bashrc` or `.zshrc`:
```
xpb () {
        curl -s -F "file=@${1:--}" https://xpb.no/paste | jq
}
```
Package `jq` required for json decoding.

Usage:
```
$ cat rpi-project.py | xpb
```

Response:
```
{
  "status": "ok",
  "message": "Paste successfully created",
  "length": 1759,
  "url": "https://xpb.no/6tmitq"
}
```

### Using `redis-cli`
With this you can set the keys manually:
```
$ redis-cli -n <db-id> set about "$(cat readme.md)"
```

Or make pastes persistent:
```
$ redis-cli -n <db-id> persist <paste-key>
```

## Change syntax language
Add `/` and the syntax language to the paste URL:
```
https://xpb.no/6tmitq/md
```

Use syntax `raw` to return a plain text document.

List of available languages here: https://xpb.no/syntax (redirects to [highlight.js docs](https://highlightjs.readthedocs.io/en/latest/css-classes-reference.html#language-names-and-aliases))

## Special keys/URLs
* `about`: shown on the homepage
* `stats`: returns a json paste with statistics

## Expiration
Pastes are set to expire 90 days after initial post, this is kicked back to 90 days each time the paste is viewed (unless the paste is persistent).

## License
xpb is open-sourced software licensed under the [MIT license](LICENSE).
