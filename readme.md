# µPaste
µPaste (micro paste) is a dead simple pastebin, built with [Lumen](https://lumen.laravel.com/).

> This is still very much a work in progress, use at own risk.

## Features
* Redis database
* Deletes pastes not accessed in a year
* Syntax highlighting with overrideable language

## Requirements
* nginx (not tested with Apache)
* Redis-server
* Everything else that [Lumen](https://lumen.laravel.com/) needs

## Install
TBD

## Add paste
### Alias
Put this in your `.bashrc` or `.zshrc`:
```
upaste () {
        curl -s -F "file=@${1:--}" https://p.uctrl.net/paste | jq
}
```
Package `jq` required for json decoding.

Usage:
```
$ cat my-image.jpg | uimg
```

Response:
```
{
  "status": "ok",
  "message": "Paste successfully created",
  "length": 1759,
  "url": "https://p.uctrl.net/6tmitq"
}
```

### Using `redis-cli`
With this you can set the keys manually:
```
$ redis-cli -n <db-id> set about $(cat readme.md)
```

## Change syntax language
Add `/` and the syntax language to the paste URL:
```
"https://p.uctrl.net/6tmitq/md"
```

List of available languages here: https://highlightjs.readthedocs.io/en/latest/css-classes-reference.html#language-names-and-aliases

## License
µPaste is open-sourced software licensed under the [MIT license](LICENSE).
