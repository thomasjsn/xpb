<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <link rel="stylesheet" href="/styles/night-owl.min.css">
        <script src="/highlight.min.js"></script>

        <style>
            pre {
                white-space: pre-wrap;
            }
        </style>
    </head>

    <body bgcolor="#011627">
        <pre><code @if(!is_null($syntax)) class="{{ $syntax }}" @endif>{{ $content }}</code></pre>

        <script>hljs.highlightAll();</script>
    </body>

</html>
