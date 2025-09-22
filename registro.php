<?php
session_start();
require_once "database.php";
require_once "funcoes.php";

$username = $email = $senha = $confirmar = "";
$username_err = $email_err = $senha_err = $confirmar_err = "";

// Se formulário enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- VALIDAR username ---
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor, insira seu username.";
    } elseif (!validate_nickname(trim($_POST["username"]))) {
        $username_err = "O username deve conter entre 3 a 50 caracteres.";
    } else {
        $sql = "SELECT idcliente FROM usuarios WHERE username = ?";
        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "Este username já está cadastrado.";
                } else {
                    $username = trim($_POST["username"]);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    // --- VALIDAR EMAIL ---
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, insira seu email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Formato de email inválido.";
    } else {
        $sql = "SELECT idcliente FROM usuarios WHERE email = ?";
        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "Este email já está cadastrado.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    // --- VALIDAR SENHA ---
    if (empty(trim($_POST["password"]))) {
        $senha_err = "Por favor, insira uma senha.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $senha_err = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $senha = trim($_POST["password"]);
    }

    // --- CONFIRMAR SENHA ---
    if (empty(trim($_POST["confirm_password"]))) {
        $confirmar_err = "Confirme sua senha.";
    } else {
        $confirmar = trim($_POST["confirm_password"]);
        if (empty($senha_err) && ($senha != $confirmar)) {
            $confirmar_err = "As senhas não coincidem.";
        }
    }

    // --- INSERIR NO BANCO ---
    if (empty($username_err) && empty($email_err) && empty($senha_err) && empty($confirmar_err)) {

        // --- VERIFICA SE É O GERENTE ---
        if ($username === "dontes") {
            $tipo = "gerente"; // só cria gerente se o username for "dontes"
        } else {
            $tipo = "cliente"; // clientes normais
        }

        $sql = "INSERT INTO usuarios (username, email, senha, tipo) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_email, $param_senha, $param_tipo);
            $param_username = $username;
            $param_email = $email;
            $param_senha = password_hash($senha, PASSWORD_DEFAULT);
            $param_tipo = $tipo;

            if (mysqli_stmt_execute($stmt)) {
                redirect("login.php");
            } else {
                echo "Erro ao registrar: " . mysqli_error($conexao);
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conexao);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - Nexus</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="anel">
<i style="--clr:#69B578;"></i>
<i style="--clr:#247BA0;"></i>
<i style="--clr:#2c0597;"></i>
<div class="login">
    <h2>Registrar</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div class="inputBx">
            <input type="text" name="username" placeholder="username" value="<?php echo htmlspecialchars($username); ?>">
            <span class="error-message"><?php echo $username_err; ?></span>
        </div>

        <div class="inputBx">
            <input type="email" name="email" placeholder="E-mail" value="<?php echo htmlspecialchars($email); ?>">
            <span class="error-message"><?php echo $email_err; ?></span>
        </div>

        <div class="inputBx">
            <input type="password" name="password" placeholder="Senha">
            <span class="error-message"><?php echo $senha_err; ?></span>
        </div>

        <div class="inputBx">
            <input type="password" name="confirm_password" placeholder="Confirmar Senha">
            <span class="error-message"><?php echo $confirmar_err; ?></span>
        </div>

        <div class="inputBx">
            <input type="submit" value="Registrar">
        </div>

        <div class="links">
            <a href="login.php">Já tenho conta</a>
        </div>
    </form>
</div>
</div>
</body>
</html>
