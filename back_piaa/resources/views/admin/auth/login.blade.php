<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: var(--color-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            margin: 1rem;
        }

        .login-header {
            background: var(--text-color);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header-content {
            position: relative;
            z-index: 2;
        }

        .login-icon {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .login-title {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            font-weight: 300;
        }

        .login-form {
            padding: 2.5rem 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: var(--brun-fonce);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-label i {
            color: var(--color-accent);
            margin-right: 0.5rem;
            width: 16px;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--gris);
            border-radius: var(--border-radius);
            background: white;
            color: var(--brun-fonce);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Gilroy', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(159, 92, 37, 0.1);
            transform: translateY(-1px);
        }

        .form-input.error {
            border-color: var(--red);
            background-color: rgba(220, 38, 38, 0.02);
        }

        .error-message {
            color: var(--red);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 0.5rem;
        }

        .login-btn {
            width: 100%;
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 1.25rem 2rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-btn:hover {
            background: var(--color-accent);
            transform: translateY(-2px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn i {
            margin-right: 0.75rem;
        }

        .alert-error {
            background-color: var(--red);
            color: white;
            border-radius: var(--border-radius);
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert-error i {
            margin-right: 0.75rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gris);
        }

        .copyright {
            color: var(--brun-clair);
            font-size: 0.85rem;
        }

        /* Animation d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Styles pour le champ mot de passe avec icône œil */
        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--brun-clair);
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: var(--color-accent);
        }

        .password-input {
            padding-right: 3rem !important;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-form {
                padding: 2rem 1.5rem;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="login-container">
    <div class="login-card">
        <!-- En-tête -->
        <div class="login-header">
            <div class="login-header-content">
                <div class="login-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="login-title">Espace Administrateur</h1>
                <p class="login-subtitle">Accès sécurisé au panel d'administration</p>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="login-form">
            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>Adresse email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           class="form-input @error('email') error @enderror"
                           placeholder="admin@exemple.com"
                           required
                           autofocus>
                    @error('email')
                        <p class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>Mot de passe
                    </label>
                    <div class="password-container">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input password-input @error('password') error @enderror"
                               placeholder="Votre mot de passe"
                               required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Bouton de connexion -->
                <button type="submit" class="login-btn">
                    Se connecter
                </button>
            </form>

            <!-- Messages d'erreur généraux -->
            @if($errors->any())
                <div class="alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="font-medium">Un problème est survenu. Veuillez réessayer.</span>
                </div>
            @endif

            <!-- Footer -->
            <div class="login-footer">
                <p class="copyright">
                    © 2025 PIAA • Tous droits réservés.
                </p>
                <p class="text-xs text-[#a1a09a]">Version 1.0</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = togglePassword.querySelector('i');

            togglePassword.addEventListener('click', function() {
                // Basculer le type de champ
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Changer l'icône
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>