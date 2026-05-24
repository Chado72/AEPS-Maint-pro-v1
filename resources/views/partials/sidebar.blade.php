<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-tint me-2"></i> AEPS-Maint Pro<br>
        <span style="font-size: 0.8rem; font-weight: normal; opacity: 0.8;">ONEA Yadéga</span>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> Tableau de bord</a></li>
        
        <li class="sidebar-header text-uppercase small text-white-50 mt-3 px-3">Géographie</li>
        <li><a href="{{ route('communes.index') }}"><i class="fas fa-map-marked-alt"></i> Communes</a></li>
        <li><a href="{{ route('villages.index') }}"><i class="fas fa-village"></i> Villages</a></li>
        
        <li class="sidebar-header text-uppercase small text-white-50 mt-3 px-3">Patrimoine</li>
        <li><a href="{{ route('sites.index') }}"><i class="fas fa-water"></i> Sites AEPS/PEA</a></li>
        <li><a href="{{ route('boreholes.index') }}"><i class="fas fa-bore-hole"></i> Forages</a></li>
        <li><a href="{{ route('energy-sources.index') }}"><i class="fas fa-solar-panel"></i> Énergie</a></li>
        
        <li class="sidebar-header text-uppercase small text-white-50 mt-3 px-3">Maintenance</li>
        <li><a href="{{ route('interventions.index') }}"><i class="fas fa-tools"></i> Interventions</a></li>
        <li><a href="{{ route('spare-parts.index') }}"><i class="fas fa-boxes"></i> Magasin / Pièces</a></li>
        
        <li class="sidebar-header text-uppercase small text-white-50 mt-3 px-3">Outils</li>
        <li><a href="{{ route('reports.index') }}"><i class="fas fa-file-pdf"></i> Rapports PDF</a></li>
        <li><a href="{{ route('ai.chat') }}"><i class="fas fa-robot"></i> Assistant IA</a></li>
        
        <li class="sidebar-header text-uppercase small text-white-50 mt-3 px-3">Administration</li>
        <li><a href="{{ route('settings.index') }}"><i class="fas fa-cogs"></i> Paramètres</a></li>
        <li>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link text-white w-100 text-start px-3 py-2 text-decoration-none">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </form>
        </li>
    </ul>
</div>