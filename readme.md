# xpb
__xpb__ is a pretty simple pastebin and URL shortener, built with [Lumen](https://lumen.laravel.com/).

It can receive and return all types of data, text or binary. It has no special logic for types of data, and doesn't understand what the data is. If the MIME type is specified when uploading, it will be set as content-type when serving the data.

This makes __xpb__ very flexible — you can upload simple text pastes, and have them displayed as code with syntax highlighting. Or you can upload and serve files; with custom pasts and filenames. This means that it is possible for __xpb__ to host websites.

For MIME types listed in `config/xpb.php` the file extension will be added to the URL, this is only to indicate to the user that it's a file, or a certain type. It has no effect, in fact everything after the `.` is simply stripped.

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

Headers:
* `X-API-Key`: API key

Parameters:
* `file`: paste content (required)
* `hash`: set custom hash key, allowed characters: `[A-Za-z0-9_/-]`
* `prefix`: adds `prefix/` in front of the hash key
* `mime`: specify content mime, like `image/jpeg`
* `ttl`: set time to live, in seconds
* `hlen`: hash key length, default: 6

### Alias
Put this in your `.bashrc` or `.zshrc`:
```bash
xpb () {
  curl -s -H "X-API-Key: key" -F "file=@${1:--}" https://example.com/paste | jq
}
```
Package `jq` required for json decoding.

Usage:
```
$ cat rpi-project.py | xpb
```

Response:
```json
{
  "status": "ok",
  "type": "paste",
  "length": 1759,
  "size": "1.72 KiB",
  "mime": "text/plain",
  "ttl": 15552000,
  "retention": 180,
  "url": "https://example.com/6tmitq"
}
```

### Image upload
Upload image with MIME, copy url to clipboard.

```bash
IMG=$1
MIME=`file -b --mime-type "$IMG"`

URL=`curl -s -H "X-API-Key: key" -F "file=@$IMG" -F "mime=$MIME" \
    https://example.com/paste`

echo $URL | jq
echo $URL | jq -r .url | xclip -i -sel clipboard
```

### Python script
Usage: `echo "test" | xpb.py --ttl 0`

```python
import argparse, sys, requests, json

parser=argparse.ArgumentParser()

parser.add_argument('--hash', help='Set custom hash key')
parser.add_argument('--prefix', help='Add prefix/ to hash key')
parser.add_argument('--mime', help='Specify content mime')
parser.add_argument('--ttl', help='Content expire, 0 = never')
parser.add_argument('--hlen', help='Set hash key length')

args=parser.parse_args()

url = 'https://example.com/paste'
key = 'key'

r = requests.post(url,
        files={'file': sys.stdin},
        data=vars(args),
        headers={'X-API-Key': key}
        )

print(json.dumps(r.json(), indent=2))
```

## Change syntax language
Add `?` and the syntax language to the paste URL:
```
https://example.com/6tmitq?md
```

Use syntax `raw`, `plain`, or `text` to return a plain text document.

List of available languages here: https://github.com/highlightjs/highlight.js/blob/master/SUPPORTED_LANGUAGES.md

## Special keys/URLs
* `home`: shown on the homepage
* `stats`: returns a json paste with statistics

## Retention
Retention is calculated with this formula, from https://0x0.st/

    retention = min_age + (-max_age + min_age) * pow((file_size / max_size - 1), 3)

Pastes are set to expire according to the above calculation after initial post, unless `ttl` is set in POST request. This is kicked back each time the paste is viewed (unless the paste is persistent).

## nginx config
```nginx
server {
  listen 80;
  server_name _;
  root /var/www/xpb/public;
  index index.php;

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php7.4-fpm.sock;
  }

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }
}
```

## Author
**Thomas Jensen**
* Twitter: [@thomasjsn](https://twitter.com/thomasjsn)
* Github: [@thomasjsn](https://github.com/thomasjsn)
* Website: [cavelab.dev](https://cavelab.dev)

## License
The MIT License (MIT). Please see [license file](LICENSE.txt) for more information.
