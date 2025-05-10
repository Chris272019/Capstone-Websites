<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Blood Bank Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }

        .login-container {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .login-image {
            flex: 1;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat center center;
            background-size: cover;
            opacity: 0.1;
        }

        .login-image h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .login-image p {
            font-size: 1.1em;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
            opacity: 0.9;
        }

        .medical-icon {
            font-size: 4em;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .login-form {
            flex: 1;
            padding: 50px;
            background: white;
        }

        .login-form h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2em;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            border-color: #e74c3c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 12px;
            background: white;
            padding: 0 5px;
            color: #e74c3c;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .error {
            color: #e74c3c;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-image {
                padding: 30px;
            }

            .login-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <div class="medical-icon">🏥</div>
            <h1>Lifestream Admin login</h1>
            <p>Secure access to manage blood donation operations and maintain critical medical records.</p>
        </div>
        <div class="login-form">
            <h2>Administrator Login</h2>
            <form action="process_admin_login.php" method="POST">
                <div class="form-group">
                    <input type="text" name="username" id="username" placeholder=" " required>
                    <label for="username">Username</label>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="login-btn">Login to Dashboard</button>
            </form>
        </div>
    </div>

    <script>
        // Add floating label functionality
        document.querySelectorAll('.form-group input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>
