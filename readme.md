# xpb
xpb is a dead simple pastebin and URL shortener, built with [Lumen](https://lumen.laravel.com/).

> This is still very much a work in progress, use at own risk.

## Features
* Redis database
* Deletes pastes not accessed in 3 years
* Syntax highlighting with overrideable language

## Requirements
* PHP >= 7.1.3
* nginx (not tested with Apache)
* Redis-server

## Install
Packages required:

* nginx
* php7.x
* php7.x-mbstring
* php7.x-xml
* php7.x-zip
* redis-server
* composer
* unzip

## Add paste
If paste is a URL; xpb will redirect instead of showing the content — acting as a URL shortener.

### Alias
Put this in your `.bashrc` or `.zshrc`:
```
xpb () {
        curl -s -F "file=@${1:--}" https://example.com/paste | jq
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
  "url": "https://example.com/6tmitq"
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
https://example.com/6tmitq/md
```

Use syntax `raw` to return a plain text document.

List of available languages here: https://highlightjs.readthedocs.io/en/latest/css-classes-reference.html#language-names-and-aliases

## Special keys/URLs
* `about`: shown on the homepage
* `stats`: returns a json paste with statistics

## Expiration
Pastes are set to expire 180 days after initial post, this is kicked back to 3 years each time the paste is viewed (unless the paste is persistent).

## License
xpb is open-sourced software licensed under the [MIT license](LICENSE).
