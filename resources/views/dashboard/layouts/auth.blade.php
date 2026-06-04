<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TK Ibnul Qoyyim')</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #2ECC71;
            --green-dark: #1a9e52;
            --green-light: #d4f5e3;
            --blue: #4ECDC4;
            --bg: #FFFDF5;
            --dark: #2D3436;
            --gray: #636E72;
        }

        * { box-sizing: border-box; margin:0; padding:0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(160deg, #FFFDF5 0%, #e8faf0 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-wrapper {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            border: 3px solid var(--green-light);
            box-shadow: 0 20px 60px rgba(46,204,113,0.15);
        }

        .auth-title {
            font-family: 'Fredoka One', cursive;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .auth-sub {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 16px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 14px;
            border: 2px solid var(--green-light);
            background: #F0FFF4;
            font-weight: 600;
            outline: none;
            font-family: 'Nunito', sans-serif;
        }

        input[type="checkbox"] {
            width: auto;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--green);
        }

        input:focus {
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 4px rgba(46,204,113,0.12);
        }

        input[type="checkbox"]:focus {
            box-shadow: none;
        }

        .remember label {
            cursor: pointer;
            user-select: none;
            margin: 0;
            padding: 0;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        button {
            width: 100%;
            padding: 14px;
            border-radius: 50px;
            border: none;
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            color: white;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(46,204,113,0.4);
            transition: 0.3s;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(46,204,113,0.5);
        }

        .link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .link a {
            color: var(--green-dark);
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 30px 20px;
                border-radius: 20px;
            }

            .auth-title {
                font-size: 22px;
            }

            input {
                padding: 10px 14px;
            }

            button {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        @yield('content')
    </div>
</body>
</html>
