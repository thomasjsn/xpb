# xpb
xpb is a pretty simple pastebin and URL shortener, built with [Lumen](https://lumen.laravel.com/).

> This is still very much a work in progress, use at own risk.

## Features
* Redis database
* Pasts expire if not access within TTL setting
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

Parameters:
* `file`: paste content (required)
* `mime`: specify content mime, like `image/jpeg`
* `ttl`: set time to live, in seconds
* `hlen`: hash key length, default: 6
* `hash`: set custom hash key

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
  "size": "1.72 KiB"
  "mime": "text/plain",
  "ttl": 15552000,
  "ttl_d": 180,
  "is_link": false,
  "url": "https://example.com/6tmitq"
}
```

### Image upload
Upload image with 7 days TTL, copy url to clipboard.

```
IMG=$1
MIME=`file -b --mime-type "$IMG"`

URL=`curl -s -F "file=@$IMG" -F "mime=$MIME" \
    https://example.com/paste`

echo $URL | jq
echo $URL | jq -r .url | xclip -i -sel clipboard
```

### Python script
Usage: `echo "test" | xpb.py --ttl 0`

```
import argparse, sys, requests, json

parser=argparse.ArgumentParser()

parser.add_argument('--mime', help='Specify content mime')
parser.add_argument('--ttl', help='Content expire, 0 = never')
parser.add_argument('--hlen', help='Set hash key length')
parser.add_argument('--hash', help='Set custom hash key')

args=parser.parse_args()

url = 'https://example.com/paste'
r = requests.post(url, files={'file': sys.stdin}, data=vars(args))

print(json.dumps(r.json(), indent=2))

```

## Change syntax language
Add `/` and the syntax language to the paste URL:
```
https://example.com/6tmitq/md
```

Use syntax `raw` to return a plain text document.

List of available languages here: https://github.com/highlightjs/highlight.js/blob/master/SUPPORTED_LANGUAGES.md

## Special keys/URLs
* `home`: shown on the homepage
* `stats`: returns a json paste with statistics

## Retention
Retention is calculated with this formula, from https://0x0.st/

   retention = min_age + (-max_age + min_age) * pow((file_size / max_size - 1), 3)

Pastes are set to expire according to the above calculation after initial post, unless `ttl` is set in POST request. This is kicked back each time the paste is viewed (unless the paste is persistent).

## License
xpb is open-sourced software licensed under the [MIT license](LICENSE).

## Author
[Thomas Jensen](https://thomas.stdout.no)
