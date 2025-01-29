<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Admin Login - Blood Donation System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .login-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 300px;
            border: 2px solid #e6e6e6;
        }
        .login-form h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #c0392b; /* Blood red */
        }
        .login-form input {
            width: 89%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-form input:focus {
            border-color: #c0392b;
        }
        .login-form button {
            width: 100%;
            padding: 12px;
            background-color: #c0392b; /* Blood red */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-form button:hover {
            background-color: #e74c3c;
        }
        .login-form .remember-me {
            display: flex;
            align-items: center;
        }
        .login-form .remember-me input {
            margin-right: 8px;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        .login-form p {
            text-align: center;
            color: #555;
            margin-top: 10px;
        }
        .login-form p a {
            color: #c0392b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <form class="login-form" action="process_admin_login.php" method="POST">
        <h2>Admin Login</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        
    
        <button type="submit">Login</button>
    </form>
</body>
</html>
