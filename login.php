<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Dulur CAFE</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;

            background:
                radial-gradient(circle at 20% 20%, #20c997 0%, transparent 30%),
                radial-gradient(circle at 80% 80%, #0f5132 0%, transparent 35%),
                linear-gradient(135deg, #198754, #0f5132);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
            overflow: hidden;
        }

        /* Dekorasi background */
        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            z-index: 0;
        }

        body::before {
            width: 350px;
            height: 350px;
            top: -150px;
            left: -100px;
        }

        body::after {
            width: 450px;
            height: 450px;
            bottom: -220px;
            right: -150px;
        }

        /* CARD */
        .login-card {
            position: relative;
            z-index: 1;

            width: 420px;
            max-width: 100%;

            background: rgba(255, 255, 255, 0.97);

            border-radius: 25px;

            overflow: hidden;

            box-shadow:
                0 25px 60px rgba(0,0,0,0.30);

            animation: muncul 0.7s ease;
        }

        @keyframes muncul {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* HEADER */
        .login-header {
            position: relative;

            text-align: center;

            color: white;

            padding: 35px 20px 40px;

            background:
                linear-gradient(
                    135deg,
                    #146c43,
                    #198754,
                    #20c997
                );
        }

        /* Dekorasi header */
        .login-header::after {
            content: "";

            position: absolute;

            width: 120%;
            height: 50px;

            bottom: -25px;
            left: -10%;

            background: white;

            border-radius: 50%;
        }

        /* LOGO */
        .logo {
            width: 90px;
            height: 90px;

            margin: auto;

            border-radius: 50%;

            background: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 48px;

            box-shadow:
                0 10px 25px rgba(0,0,0,0.20);

            animation: melayang 3s ease-in-out infinite;
        }

        @keyframes melayang {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-7px);
            }
        }

        .brand {
            margin-top: 18px;

            font-size: 32px;

            font-weight: bold;

            letter-spacing: 1px;
        }

        .subtitle {
            margin-top: 5px;

            font-size: 13px;

            opacity: 0.9;
        }

        /* BODY */
        .login-body {
            padding: 35px;
        }

        .welcome {
            text-align: center;

            margin-bottom: 25px;
        }

        .welcome h2 {
            color: #333;

            font-size: 21px;

            margin-bottom: 7px;
        }

        .welcome p {
            color: #777;

            font-size: 13px;
        }

        /* LABEL */
        label {
            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: bold;

            color: #444;
        }

        /* INPUT */
        .form-group {
            margin-bottom: 20px;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;

            height: 50px;

            border: 1px solid #ddd;

            border-radius: 12px;

            padding: 0 15px;

            font-size: 14px;

            outline: none;

            transition: 0.3s;

            background: #fff;
        }

        input:focus {
            border-color: #198754;

            box-shadow:
                0 0 0 4px rgba(25,135,84,0.12);
        }

        input::placeholder {
            color: #aaa;
        }

        /* PASSWORD */
        .password-input {
            padding-right: 55px;
        }

        .show-password {
            position: absolute;

            right: 8px;
            top: 50%;

            transform: translateY(-50%);

            width: 40px;
            height: 40px;

            border: none;

            background: transparent;

            border-radius: 8px;

            font-size: 20px;

            cursor: pointer;

            transition: 0.2s;
        }

        .show-password:hover {
            background: #e9f7ef;
        }

        /* LOGIN BUTTON */
        .btn-login {
            width: 100%;

            height: 50px;

            border: none;

            border-radius: 12px;

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #20c997
                );

            box-shadow:
                0 8px 18px rgba(25,135,84,0.25);

            transition: 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(25,135,84,0.35);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        /* FOOTER */
        .login-footer {
            padding: 16px;

            text-align: center;

            background: #f8f9fa;

            border-top: 1px solid #eee;

            color: #888;

            font-size: 12px;
        }

        .cafe-name {
            color: #198754;

            font-weight: bold;
        }

        /* RESPONSIVE */
        @media (max-width: 480px) {

            body {
                padding: 15px;
            }

            .login-card {
                border-radius: 20px;
            }

            .login-header {
                padding: 30px 15px 35px;
            }

            .logo {
                width: 75px;
                height: 75px;

                font-size: 40px;
            }

            .brand {
                font-size: 27px;
            }

            .login-body {
                padding: 28px 22px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card">

        <!-- HEADER -->
        <div class="login-header">

            <div class="logo">
                ☕
            </div>

            <div class="brand">
                Dulur CAFE
            </div>

            <div class="subtitle">
                Sistem Kasir & Manajemen Cafe
            </div>

        </div>


        <!-- BODY -->
        <div class="login-body">

            <div class="welcome">

                <h2>
                    Wilujeng Sumping 👋
                </h2>

                <p>
                    Mangga Login
                </p>

            </div>


            <form action="proses_login.php" method="POST">

                <!-- USERNAME -->
                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required>

                </div>


                <!-- PASSWORD -->
                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="password-input"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required>

                        <button
                            type="button"
                            class="show-password"
                            id="togglePassword"
                            onclick="lihatPassword()"
                            aria-label="Tampilkan password">

                            👁️

                        </button>

                    </div>

                </div>


                <!-- LOGIN -->
                <button
                    type="submit"
                    class="btn-login">

                   Login

                </button>

            </form>

        </div>





    <script>

        function lihatPassword() {

            const password =
                document.getElementById("password");

            const tombol =
                document.getElementById("togglePassword");


            if (password.type === "password") {

                password.type = "text";

                tombol.innerHTML = "👁️";

                tombol.setAttribute(
                    "aria-label",
                    "Sembunyikan password"
                );

            } else {

                password.type = "password";

                tombol.innerHTML = "👁️";

                tombol.setAttribute(
                    "aria-label",
                    "Tampilkan password"
                );

            }

        }

    </script>

</body>
</html>
