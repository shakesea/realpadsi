<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-container">

    {{-- Left illustration --}}
    <div class="login-left">
        <img src="{{ asset('img/NUTAPOS_Login.png') }}" alt="Login Image">
    </div>

    <div class="login-right">
        <div class="logo-wrap">
            <img src="{{ asset('img/NUTAPOS_Logo.png') }}" class="logo-icon">
            <span class="logo-text">nutapos</span>
        </div>

        <h2>Selamat Datang!</h2>
        <h5>Bersiaplah untuk memberikan pelayanan terbaik hari ini.</h5>

        <form>
            <div class="input-group">
                <span class="icon"></span>
                <input type="text" placeholder="Username">
            </div>

            <div class="input-group">
                <span class="icon"></span>
                <input type="password" placeholder="Password">
            </div>

            <button type="submit" class="btn-login">Masuk</button>
        </form>

    </div>
</div>

</body>
</html> -->


<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NutaPOS</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    Tambahkan Font Awesome untuk icons
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- <div class="login-container">
    {{-- Left illustration --}}
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

        {{-- Alert Error --}}
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif -->

        <!-- @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

            @csrf
            <div class="input-group {{ $errors->has('username') ? 'input-error' : '' }}">
                <i class="fas fa-user icon"></i>
                <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required>
            </div>

            <div class="input-group {{ $errors->has('password') ? 'input-error' : '' }}">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

    </div>
</div>

</body>
</html> --> 

<!-- 
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

        {{-- Tampilkan error --}}
        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ $errors->first('error') }}</span>
            </div>
        @endif


            @csrf
            <div class="input-group">
                <i class="fas fa-user icon"></i>
                <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <!-- {{-- Info credentials untuk testing --}}
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 12px; color: #666;">
            <strong>Testing Credentials:</strong><br>
            Username: <strong>manager01</strong><br>
            Password: <strong>admin123</strong>
        </div>
    </div> -->
<!-- </div>

</body>
</html> --> 


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

        {{-- CARA 1: Tampilkan semua errors --}}
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

        {{-- CARA 2: Tampilkan error specific --}}
        {{-- @error('error')
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror --}}

            @csrf
            <div class="input-group">
                <i class="fas fa-user icon"></i>
                <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <!-- {{-- Info untuk testing --}}
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 12px; color: #666; text-align: center;">
            <strong>Testing:</strong><br>
            Username: manager01 | Password: admin123
        </div>
    </div> -->
</div>

</body>
</html>