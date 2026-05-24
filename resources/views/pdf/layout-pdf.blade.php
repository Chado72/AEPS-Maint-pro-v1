<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Rapport AEPS-Maint Pro')</title>
    <style>
        @page {
            margin-top: 60px;
            margin-bottom: 60px;
            margin-left: 20px;
            margin-right: 20px;
        }
        body {
            font-family: 'dejavu sans', sans-serif; /* Police compatible UTF-8 */
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            position: fixed;
            top: -50px;
            left: 0;
            right: 0;
            height: 50px;
            border-bottom: 2px solid #0056b3; /* Bleu ONEA */
            display: table;
            width: 100%;
        }
        .header .logo {
            display: table-cell;
            vertical-align: middle;
            width: 100px;
            text-align: left;
        }
        .header .logo img {
            max-height: 40px;
        }
        .header .title {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            color: #0056b3;
            text-transform: uppercase;
        }
        .header .meta {
            display: table-cell;
            vertical-align: middle;
            width: 150px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .content {
            margin-top: 20px;
        }
        h1 { font-size: 18px; color: #0056b3; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        h2 { font-size: 14px; color: #444; margin-top: 15px; background: #f0f4f8; padding: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { padding: 2px 6px; border-radius: 4px; color: white; font-size: 10px; }
        .bg-success { background-color: #28a745; }
        .bg-danger { background-color: #dc3545; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .signature-box {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-cell {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
            border: 1px solid #ccc;
            padding: 10px;
            height: 80px;
        }
    </style>
</head>
<body>

    <!-- En-tête fixe -->
    <div class="header">
        <div class="logo">
            <!-- Remplacez par le chemin réel de votre logo ou un texte si pas d'image -->
            <strong>ONEA</strong>
        </div>
        <div class="title">
            AEPS-Maint Pro Ouahigouya<br>
            <span style="font-size:10px; font-weight:normal;">Province du Yadéga</span>
        </div>
        <div class="meta">
            Date: {{ date('d/m/Y') }}<br>
            Page: {PAGE_NUM} / {PAGE_COUNT}
        </div>
    </div>

    <!-- Contenu dynamique -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Pied de page fixe -->
    <div class="footer">
        Généré par AEPS-Maint Pro &copy; {{ date('Y') }} - Usage interne ONEA
    </div>

</body>
</html>