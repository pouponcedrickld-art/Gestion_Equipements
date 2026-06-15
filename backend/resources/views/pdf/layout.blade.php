<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1e293b;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #3b82f6;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Rapport' }}</h1>
        <p>Généré le {{ $date ?? now()->format('d/m/Y H:i') }}</p>
    </div>

    @yield('content')

    <div class="footer">
        <p>© {{ date('Y') }} Gestion des Équipements. Tous droits réservés.</p>
    </div>
</body>
</html>
