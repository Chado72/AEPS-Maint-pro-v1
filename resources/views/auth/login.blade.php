@extends('layouts.auth')

@section('title', 'Connexion - AEPS-Maint Pro')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            
            <!-- Carte de Connexion -->
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body p-5">
                    
                    <!-- En-tête avec Logo (Simulé par du texte si pas d'image) -->
                    <div class="text-center mb-4">
                        <div style="font-size: 2.5rem; color: #0056b3; font-weight: bold;">
                            ONEA
                        </div>
                        <h5 class="text-muted mt-2">AEPS-Maint Pro</h5>
                        <p class="small text-secondary">Province du Yadéga</p>
                    </div>

                    <!-- Formulaire -->
                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="nom@onea.bf" 
                                       required 
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••" 
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Options -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label small" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                            <a href="#" class="small text-decoration-none">Mot de passe oublié ?</a>
                        </div>

                        <!-- Bouton -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Pied de carte -->
                <div class="card-footer bg-white text-center py-3 border-top-0">
                    <small class="text-muted">
                        &copy; {{ date('Y') }} ONEA - Direction Provinciale du Yadéga
                    </small>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection