<!DOCTYPE html>
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

    {{-- Right panel --}}
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
</html>
