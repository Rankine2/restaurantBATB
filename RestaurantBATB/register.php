<?php
session_start();
include "db.php";

if(isset($_POST['submit'])){
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $name     = $_POST['name'];

    $check = "SELECT * FROM users WHERE email='$email'";
    $res = mysqli_query($conn, $check);

    if($res->num_rows > 0){
        $error_message = "Email already registered!";
    } else {
        $sql = "INSERT INTO users (name,email,password,type) 
                VALUES ('$name','$email','$password','user')";
        $result = mysqli_query($conn, $sql);

        if($result){
            header("Location: login.php?registered=success");
            exit();
        } else {
            $error_message = "Registration failed: ".$conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Lutong Nayon</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

/* body */
body { 
    background: url('image/bannerrt.jpg') center/cover no-repeat; 
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    overflow-x: hidden;
}

/* headr */
.header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    z-index: 2;
}

.header h1 {
    font-size: 4rem;
    font-weight: 700;
    text-shadow: 0 4px 25px rgba(0, 0, 0, 0.7);
    animation: floatText 3s ease-in-out infinite;
}

.header p {
    font-size: 1.6rem;
    font-weight: 500;
    opacity: 0.95;
    text-shadow: 0 3px 15px rgba(0, 0, 0, 0.6);
    animation: fadeInText 2s ease-in-out infinite alternate;
}

/* animations */
@keyframes floatText {
    0% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0); }
}

@keyframes fadeInText {
    0% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* reg card */
.register-card {
    width: 380px;
    background: rgba(255, 255, 255, 0.05); 
    backdrop-filter: blur(12px);
    padding: 35px 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    text-align: center;
    position: relative;
    z-index: 2;
}

/* steam effects */
.steam {
    position: absolute;
    bottom: 20px;
    width: 6px;
    height: 60px;
    background: linear-gradient(to top, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0));
    border-radius: 50%;
    animation: rise 3s infinite;
}

.steam:nth-child(1) { left: 20%; animation-delay: 0s; }
.steam:nth-child(2) { left: 35%; animation-delay: 0.5s; }
.steam:nth-child(3) { left: 50%; animation-delay: 1s; }
.steam:nth-child(4) { left: 65%; animation-delay: 1.5s; }
.steam:nth-child(5) { left: 80%; animation-delay: 2s; }

@keyframes rise {
    0% { transform: translateY(0) scaleX(1); opacity: 0; }
    50% { opacity: 1; }
    100% { transform: translateY(-140px) scaleX(1.3); opacity: 0; }
}

/* f elements */
.register-card h2 {
    margin-bottom: 12px;
    color: #fff;
    font-weight: 600;
}

.register-card input {
    width: 100%;
    padding: 13px;
    margin-top: 15px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 15px;
    transition: all 0.3s;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.register-card input::placeholder {
    color: #e0e0e0;
}

.register-card input:focus {
    border-color: #f97316;
    box-shadow: 0 0 6px rgba(249, 115, 22, 0.5);
    outline: none;
}

.register-card button {
    width: 100%;
    padding: 14px;
    margin-top: 22px;
    background: #f97316;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.register-card button:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

/* switch link */
.switch-auth {
    margin-top: 15px;
    font-size: 14px;
}

.switch-auth a {
    color: #f97316;
    font-weight: 600;
    text-decoration: none;
}

.switch-auth a:hover {
    text-decoration: underline;
}

/* error msg */
.error-message {
    color: #dc2626;
    background: #fee2e2;
    padding: 10px;
    border-radius: 8px;
    margin-top: 15px;
    font-weight: 600;
}

/* respo */
@media(max-width: 480px) {
    .header h1 { font-size: 2.5rem; }
    .header p { font-size: 1.1rem; }
    .register-card { width: 90%; padding: 25px; }
}

</style>
</head>
<body>

<!-- header -->
<div class="header">
    <h1>🍽 Lutong Nayon</h1>
    <p>Home-cooked Filipino dishes made with love</p>
</div>

<!-- reg card -->
<div class="register-card">
    <h2>Create Account</h2>

    <!-- Steam effect(register banda ofc) -->
    <div class="steam"></div>
    <div class="steam"></div>
    <div class="steam"></div>
    <div class="steam"></div>
    <div class="steam"></div>

    <!-- error msg -->
    <?php if(isset($error_message)) { ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php } ?>

    <!-- reg form -->
    <form action="" method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="submit">Register</button>
    </form>

    <div class="switch-auth">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
