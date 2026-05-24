<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <!-- Bouton Mobile -->
        <button class="btn btn-outline-primary d-md-none me-2" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <h4 class="mb-0 text-primary d-none d-md-block">@yield('page-title', 'Tableau de bord')</h4>

        <div class="ms-auto d-flex align-items-center">
            <!-- Notifications (Factice pour l'instant) -->
            <div class="dropdown me-3">
                <a href="#" class="text-secondary position-relative" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Alertes Stock</h6></li>
                    <li><a class="dropdown-item small" href="#">Pompe Submersible - Stock bas</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item small text-center" href="#">Voir tout</a></li>
                </ul>
            </div>

            <!-- Profil Utilisateur -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="d-none d-md-block">
                        <span class="d-block small fw-bold">{{ Auth::user()->name }}</span>
                        <span class="d-block" style="font-size: 0.7rem; color: #666;">{{ Auth::user()->role->name ?? 'Utilisateur' }}</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('settings.index') }}">Mon Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Formulaire de déconnexion caché -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>