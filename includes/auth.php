<?php
session_start();
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function login($email, $password) {
    global $conn;
    
    $email = $conn->real_escape_string($email);
    $sql = "SELECT id, password FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
    }
    return false;
}

function register($name, $email, $password) {
    global $conn;
    
    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
    return $conn->query($sql);
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>