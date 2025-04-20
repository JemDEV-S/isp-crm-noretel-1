@extends('core::layouts.master')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">Iniciar Sesión</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('core.auth.login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario o Email</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required autofocus>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <div class="small mb-2">
                    <a href="{{ route('core.auth.forgot-password') }}">¿Olvidaste tu contraseña?</a>
                </div>
                <div class="small">
                    ¿No tienes una cuenta? <a href="{{ route('core.auth.register') }}">Regístrate</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection