<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #3d3530;
            background: #fffdf9;
        }

        .page {
            padding: 3cm 3.5cm;
        }

        .header {
            text-align: center;
            margin-bottom: 2cm;
            padding-bottom: 1cm;
            border-bottom: 1px solid #e8d9c0;
        }

        .header h1 {
            font-family: 'DejaVu Serif', serif;
            font-size: 26pt;
            font-weight: normal;
            color: #7c6343;
            letter-spacing: 0.05em;
            margin-bottom: 0.3cm;
        }

        .header .author {
            font-family: 'DejaVu Serif', serif;
            font-size: 13pt;
            font-style: italic;
            color: #9c8060;
        }

        .content {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-top: 1cm;
        }

        .footer {
            position: fixed;
            bottom: 1.5cm;
            left: 3.5cm;
            right: 3.5cm;
            text-align: center;
            font-size: 9pt;
            color: #c4a882;
            border-top: 1px solid #e8d9c0;
            padding-top: 0.4cm;
        }

        .qa-section {
            margin-top: 1.5cm;
            padding-top: 1cm;
            border-top: 1px solid #e8d9c0;
        }

        .qa-title {
            font-family: 'DejaVu Serif', serif;
            font-size: 16pt;
            font-weight: normal;
            color: #7c6343;
            margin-bottom: 0.8cm;
        }

        .qa-item {
            margin-bottom: 0.6cm;
        }

        .qa-question {
            font-style: italic;
            color: #b09878;
            font-size: 10pt;
            margin-bottom: 0.1cm;
        }

        .qa-answer {
            color: #3d3530;
            font-size: 11pt;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Mes voeux</h1>
            <div class="author">{{ $author }}</div>
        </div>

        <div class="content">{{ $draft->generated_text }}</div>

        @if(count(array_filter($questions, fn($q) => $answers->has($q['key']))) > 0)
        <div class="qa-section">
            <div class="qa-title">Mes réponses</div>
            @foreach($questions as $q)
                @if($answers->has($q['key']))
                    <div class="qa-item">
                        <p class="qa-question">{{ $q['label'] }}</p>
                        <p class="qa-answer">{{ $answers->get($q['key']) }}</p>
                    </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <div class="footer">
        Rediges avec amour &bull; {{ now()->format("d/m/Y") }}
    </div>
</body>
</html>
