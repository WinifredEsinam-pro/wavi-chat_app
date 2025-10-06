<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - WaviChat</title>
    <link rel="icon" type="image/png" href="{{ asset('image/wavi_logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #1c3f3f, #3a6a6a);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
            padding: 1rem;
        }

        .card {
            background: rgba(255,255,255,0.05);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            width: 90%;
            max-width: 380px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-align: center;
            transition: all 0.3s ease;
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            transition: transform 0.3s ease;
        }

        .card:hover .logo {
            transform: scale(1.05);
        }

        h1 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            margin-bottom: 1.8rem;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.3);
            margin-bottom: 1rem;
            background: rgba(255,255,255,0.1);
            color: #fff;
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #25D366;
            box-shadow: 0 0 5px #25D366;
            background: rgba(255,255,255,0.15);
        }

        .btn {
            width: 100%;
            padding: 0.75rem 0;
            border-radius: 999px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-register {
            background-color: #25D366;
            color: white;
            border: none;
        }

        .btn-register:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 0 12px #25D366;
        }

        .btn-login-link {
            display: block;
            margin-top: 1rem;
            color: #25D366;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .btn-login-link:hover {
            color: #1ec95a;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .card {
                padding: 2rem 1.5rem;
            }
            .logo {
                width: 60px;
                height: 60px;
            }
            h1 {
                font-size: 1.25rem;
            }
            p.subtitle {
                font-size: 0.85rem;
            }
            input {
                padding: 0.6rem 0.8rem;
            }
            .btn {
                padding: 0.6rem 0;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .card {
                padding: 2.25rem 1.75rem;
            }
            .logo {
                width: 65px;
                height: 65px;
            }
            h1 {
                font-size: 1.35rem;
            }
            p.subtitle {
                font-size: 0.9rem;
            }
            input {
                padding: 0.65rem 0.9rem;
            }
            .btn {
                padding: 0.65rem 0;
                font-size: 0.95rem;
            }
        }

        @media (min-width: 769px) {
            .card {
                padding: 2.5rem 2rem;
            }
            .logo {
                width: 70px;
                height: 70px;
            }
            h1 {
                font-size: 1.5rem;
            }
            p.subtitle {
                font-size: 0.95rem;
            }
            input {
                padding: 0.75rem 1rem;
            }
            .btn {
                padding: 0.75rem 0;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('image/wavi_logo1.png') }}" alt="WaviChat Logo" class="logo">
        <h1>Join WaviChat!</h1>
        <p class="subtitle">Create an account and start connecting</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <input type="text" name="name" placeholder="Full Name" required autofocus>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            <button type="submit" class="btn btn-register">Register</button>
        </form>

        <a href="{{ route('login') }}" class="btn-login-link">Already have an account? Login</a>
    </div>
</body>
</html>
