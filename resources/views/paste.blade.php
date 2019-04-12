<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>µPaste</title>
        <link rel="stylesheet" href="/styles/solarized-dark.css">
        <script src="/highlight.pack.js"></script>
    </head>

    <body @if(! in_array($syntax, ["plain", "text", "nohighlight"])) bgcolor="#002b36" @endif>
        <pre><code @if(!is_null($syntax)) class="{{ $syntax }}" @endif>{{ $content }}</code></pre>

        <script>hljs.initHighlightingOnLoad();</script>
    </body>

</html>
