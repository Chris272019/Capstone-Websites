<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Hospital Blood Request Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 20px;
        }

        .login-container {
            display: flex;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .login-image {
            flex: 1;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
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
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-image p {
            font-size: 1.1em;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
            opacity: 0.9;
            line-height: 1.6;
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
            color: #1e293b;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2em;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        .form-group input:focus {
            border-color: #ef4444;
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .form-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            transition: all 0.3s ease;
            pointer-events: none;
            font-weight: 500;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 12px;
            background: white;
            padding: 0 5px;
            color: #ef4444;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            font-family: 'Inter', sans-serif;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.2);
        }

        .error {
            color: #ef4444;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }

        .hospital-info {
            margin-top: 24px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .hospital-info p {
            color: #475569;
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .hospital-info ul {
            list-style: none;
            padding: 0;
        }

        .hospital-info li {
            color: #475569;
            font-size: 14px;
            margin-bottom: 8px;
            padding-left: 24px;
            position: relative;
        }

        .hospital-info li:before {
            content: "•";
            color: #ef4444;
            position: absolute;
            left: 0;
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
            <h1>Hospital Blood Request Portal</h1>
            <p>Access the blood bank system to manage blood requests and track donations for your hospital.</p>
        </div>
        <div class="login-form">
            <h2>Hospital Login</h2>
            <form action="process_hospital_login.php" method="POST">
                <div class="form-group">
                    <input type="text" name="hospital_name" id="hospital_name" placeholder=" " required>
                    <label for="hospital_name">Hospital Name</label>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="login-btn">Access Blood Bank System</button>
            </form>

            <div class="hospital-info">
                <p>For registered hospitals only. Please contact the blood bank administrator if you need access.</p>
                <ul>
                    <li>Request blood units for emergency cases</li>
                    <li>Track blood request status</li>
                    <li>View blood availability</li>
                    <li>Manage hospital profile</li>
                </ul>
            </div>
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