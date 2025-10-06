<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Wavi') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('image/wavi_logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }

        .glass-card {
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 30px rgba(72, 169, 108, 0.5);
            padding: 3rem 2rem;
            text-align: center;
            width: 90%;
            max-width: 400px;
            transition: all 0.4s ease;
        }

        .glass-card:hover {
            box-shadow: 0 0 50px rgba(44, 79, 57, 0.8);
        }

        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            filter: drop-shadow(0 0 10px #25D366);
            transition: transform 0.4s ease;
        }

        .glass-card:hover .logo {
            transform: scale(1.05);
        }

       
        .btn {
            display: block;
            width: 100%;
            font-weight: 600;
            padding: 0.75rem 0;
            border-radius: 999px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-login {
            background-color: #25D366;
            color: white;
        }

        .btn-login:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 0 15px rgba(67, 145, 96, 0.8);
        }

        .btn-register {
            background-color: transparent;
            border: 2px solid #25D366;
            color: #25D366;
        }

        .btn-register:hover {
            transform: translateY(-4px) scale(1.05);
            background-color: #25D366;
            color: white;
            box-shadow: 0 0 15px rgba(44, 79, 57, 0.8);
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 650;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2rem;
        }

        @media (max-width: 480px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }

            .logo {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            p {
                font-size: 0.875rem;
            }

            .btn {
                padding: 0.6rem 0;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .glass-card {
                padding: 2.5rem 2rem;
            }

            .logo {
                width: 70px;
                height: 70px;
                margin-bottom: 1.25rem;
            }

            h2 {
                font-size: 1.4rem;
            }

            p {
                font-size: 1rem;
            }

            .btn {
                padding: 0.7rem 0;
                font-size: 0.95rem;
            }
        }

        @media (min-width: 769px) {
            .glass-card {
                padding: 3rem 2rem;
            }

            .logo {
                width: 80px;
                height: 80px;
                margin-bottom: 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            p {
                font-size: 1rem;
            }

            .btn {
                padding: 0.75rem 0;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <img src="{{ asset('image/wavi_logo1.png') }}" alt="App Logo" class="logo">

        <h2>Welcome to WaviChat</h2>
        <p>Connect. Chat. Flow.</p>

        <a href="{{ route('login') }}" class="btn btn-login">Login</a>
        <a href="{{ route('register') }}" class="btn btn-register">Register</a>
    </div>

</body>
</html>
