<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - AEPS-Maint Pro</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --onea-blue: #0056b3;
            --onea-dark: #003d80;
            --sidebar-width: 260px;
        }
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        /* Sidebar */
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            margin-left: 0;
            background-color: var(--onea-dark);
            color: #fff;
            position: fixed;
            transition: margin .25s ease-out;
            z-index: 1000;
        }
        #sidebar-wrapper .sidebar-heading {
            padding: 1.5rem 1.25rem;
            font-size: 1.2rem;
            font-weight: bold;
            background-color: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar-wrapper .list-group {
            width: var(--sidebar-width);
        }
        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: rgba(255,255,255,0.8);
            border: none;
            padding: 1rem 1.25rem;
        }
        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        #sidebar-wrapper .list-group-item.active {
            background-color: var(--onea-blue);
            color: #fff;
            font-weight: bold;
        }
        #sidebar-wrapper .list-group-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Page Content */
        #page-content-wrapper {
            min-width: 100vw;
            margin-left: var(--sidebar-width);
            transition: margin .25s ease-out;
        }
        
        /* Navbar */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            background-color: #fff;
        }
        .navbar-brand {
            color: var(--onea-blue);
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #page-content-wrapper {
                margin-left: 0;
            }
            body.sb-sidenav-toggled #sidebar-wrapper {
                margin-left: 0;
            }
        }
        
        /* Utility */
        .card-custom {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .text-onea { color: var(--onea-blue); }
        .bg-onea { background-color: var(--onea-blue); }
    </style>
    
    @stack('styles')
</head>
<body>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navbar -->
            @include('partials.navbar')

            <!-- Main Content -->
            <div class="container-fluid px-4 py-4">
                <!-- Flash Messages -->
                @include('partials.flash-messages')

                <!-- Dynamic Content -->
                @yield('content')
            </div>

            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js (pour les graphiques) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Toggle Sidebar
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>