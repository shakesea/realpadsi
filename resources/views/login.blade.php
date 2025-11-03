<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NutaPOS</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <img src="{{ asset('img/NUTAPOS_Login.png') }}" alt="Login Image">
    </div>

    <div class="login-right">
        <div class="logo-wrap">
            <img src="{{ asset('img/NUTAPOS_Logo.png') }}" class="logo-icon">
            <span class="logo-text">nutapos</span>
        </div>

        <h2>Selamat Datang!</h2>
        <p>Bersiaplah untuk memberikan pelayanan terbaik hari ini.</p>

        {{-- Alert error dari controller --}}
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Alert validasi dari validator --}}
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </span>
            </div>
        @endif

        {{-- FORM LOGIN --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="input-group">
                <i class="fas fa-user icon"></i>
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Username" 
                    value="{{ old('username') }}" 
                    required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Password" 
                    required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>
    </div>
</div>

</body>
</html>
