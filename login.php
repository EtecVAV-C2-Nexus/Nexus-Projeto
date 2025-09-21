<?php 
session_start();
require_once 'database.php';
require_once 'funcoes.php';

if (logado()) {
    redirect("index.php");
}

$login = $senha = "";
$login_err = $senha_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida login
    if (!isset($_POST["login"]) || empty(trim($_POST["login"]))) {
        $login_err = "Por favor, insira seu nickname.";
    } else {
        $login = trim($_POST["login"]);
    }

    // Valida senha
    if (empty(trim($_POST["password"]))) {
        $senha_err = "Por favor, insira sua senha.";
    } else {
        $senha = trim($_POST["password"]);
    }

    if (empty($login_err) && empty($senha_err)) {

        // Verifica funcionários/gerentes
        $sql_func = "SELECT idfunc, username, senha, funcao FROM funcionarios WHERE username = ?";
        if ($stmt = mysqli_prepare($conexao, $sql_func)) {
            mysqli_stmt_bind_param($stmt, "s", $param_login);
            $param_login = $login;
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $funcao);
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($senha, $hashed_password)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["nickname"] = $username;
                        $_SESSION["funcao"] = $funcao;
                        redirect("index.php");
                    } else {
                        $login_err = "Login ou senha inválidos.";
                    }
                }
            } else {
                // Verifica clientes pelo nickname
                $sql_cli = "SELECT id, username, senha, tipo FROM usuarios WHERE username = ?";
                if ($stmt_cli = mysqli_prepare($conexao, $sql_cli)) {
                    mysqli_stmt_bind_param($stmt_cli, "s", $param_login);
                    $param_login = $login;
                    mysqli_stmt_execute($stmt_cli);
                    mysqli_stmt_store_result($stmt_cli);

                    if (mysqli_stmt_num_rows($stmt_cli) == 1) {
                        mysqli_stmt_bind_result($stmt_cli, $id, $username, $hashed_password, $tipo);
                        if (mysqli_stmt_fetch($stmt_cli)) {
                            if (password_verify($senha, $hashed_password)) {
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["nickname"] = $username;
                                $_SESSION["funcao"] = $tipo;
                                redirect("index.php"); // Página do cliente
                            } else {
                                $login_err = "Login ou senha inválidos.";
                            }
                        }
                    } else {
                        $login_err = "Login ou senha inválidos.";
                    }
                    mysqli_stmt_close($stmt_cli);
                }
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
    <title>Login - Nexus</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="anel">
    <i style="--clr:#69B578;"></i>
    <i style="--clr:#247BA0;"></i>
    <i style="--clr:#2c0597;"></i>
    <div class="login">
        <h2>Login</h2>

        <?php if (!empty($login_err)) echo '<div class="error-message">' . $login_err . '</div>'; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="inputBx">
                <input type="text" name="login" placeholder="Nickname (ex: nome@123)" value="<?php echo htmlspecialchars($login); ?>">
            </div>
            <div class="inputBx">
                <input type="password" name="password" placeholder="Senha">
            </div>
            <div class="inputBx">
                <input type="submit" value="Entrar">
            </div>
        </form>

        <!-- Link de registro visível e centralizado -->
        <div class="links">
            <a href="registro.php">Quero me registrar</a>
        </div>
    </div>
</div>
</body>
</html>
