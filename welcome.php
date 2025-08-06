<?php
session_start();

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $validUser = 'user';
    $validPass = '12345';

    if ($user === $validUser && $pass === $validPass) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $loginError = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Welcome - Restaurant Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body {
    background: url('images/restaurant-bg.jpeg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
    color: white;
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
  }

  .login-container {
    background: rgba(0, 0, 0, 0.7);
    padding: 40px;
    border-radius: 12px;
    width: 350px;
    box-shadow: 0 0 20px rgba(0,0,0,0.7);
  }

  .logo {
    width: 80px;
    height: 80px;
    margin-bottom: 15px;
  }

  h1 {
    font-size: 40px;
    color: #2E8B57;
    margin-bottom: 10px;
  }

  h2 {
    font-style: italic;
    font-size: 18px;
    margin-bottom: 30px;
  }

  input[type="text"], input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0 20px 0;
    border: none;
    border-radius: 6px;
    font-size: 16px;
  }

  input[type="submit"] {
    background-color: #2E8B57;
    color: #ffffff;
    font-weight: bold;
    border: none;
    padding: 12px 30px;
    font-size: 18px;
    cursor: pointer;
    border-radius: 6px;
  }

  .error {
    color: #ff4c4c;
    font-weight: bold;
    margin-bottom: 10px;
  }

  footer {
    margin-top: 30px;
    font-size: 14px;
    color: #ddd;
  }

  .social-icons {
    margin-top: 25px;
  }

  .social-icons a {
    color: white;
    margin: 0 10px;
    font-size: 22px;
    text-decoration: none;
    transition: color 0.3s;
  }

  .social-icons a:hover {
    background-color: #2E8B57;
  }
</style>
</head>
<body>

<div class="login-container">
    <!-- Optional Logo -->
    <!-- <img src="restaurant/logo.png" alt="Logo" class="logo"> -->

    <h1>بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</h1>
    <h2>Welcome to<br>Restaurant Management System</h2>

    <?php if($loginError): ?>
        <div class="error"><?=htmlspecialchars($loginError)?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required autofocus />
        <input type="password" name="password" placeholder="Password" required />
        <input type="submit" value="Login" />
    </form>
    <footer>
        &copy; <?=date('Y')?> | May Allah bless your meal & your efforts
    </footer>
</div>

</body>
</html>
