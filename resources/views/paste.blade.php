<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <link rel="stylesheet" href="/styles/solarized-dark.css">
        <script src="/highlight.pack.js"></script>

        <style>
            pre {
                white-space: pre-wrap;
            }
        </style>
    </head>

    <body bgcolor="#002b36">
        <pre><code @if(!is_null($syntax)) class="{{ $syntax }}" @endif>{{ $content }}</code></pre>

        <script>hljs.initHighlightingOnLoad();</script>
    </body>

</html>
