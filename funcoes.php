<?php
function redirect($location) {
    header("Location: " . $location);
    exit();
}

function logado() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

//Validar nome
function validate_nickname($nickname) {
    return !empty($nickname) && strlen($nickname) >= 3 && strlen($nickname) <= 50 && preg_match('/^[a-zA-Z0-9_]+$/', $nickname);
}

//Validar senha
function validate_password($password) {
    return !empty($password) && strlen($password) >= 8 && strlen($password) <= 16 && preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password) && !preg_match('/[^a-zA-Z0-9]/', $password);
}

//Validar hash de senha
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

//Comparar senha com hash
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}
function gerente() {
    return isset($_SESSION["funcao"]) && $_SESSION["funcao"] === "gerente";
}

function repositor() {
    return isset($_SESSION["funcao"]) && $_SESSION["funcao"] === "repositor";
}
?>