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
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Mes voeux</h1>
            <div class="author">{{ $author }}</div>
        </div>

        <div class="content">{{ $draft->generated_text }}</div>
    </div>

    <div class="footer">
        Rediges avec amour &bull; {{ now()->format("d/m/Y") }}
    </div>
</body>
</html>
