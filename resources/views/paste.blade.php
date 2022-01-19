<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <link rel="stylesheet" href="/styles/a11y-dark.min.css">
        <script src="/highlight.min.js"></script>

        <style>
            pre {
                white-space: pre-wrap;
            }
        </style>
    </head>

    <body bgcolor="#2b2b2b">
        <pre><code @if(!is_null($syntax)) class="{{ $syntax }}" @endif>{{ $content }}</code></pre>

        <script>hljs.highlightAll();</script>
    </body>

</html>
