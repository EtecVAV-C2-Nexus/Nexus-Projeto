<?php
// Inclui os arquivos de configuração do banco de dados e funções auxiliares

require_once 'database.php';
require_once 'funcoes.php';

// Inicia a sessão se ainda não estiver iniciada

// Se o usuário já estiver logado, redireciona para a página principal de jogos
if (logado()) {
    redirect("index.php");
}

// Inicializa variáveis para nome de usuário, senha e mensagens de erro
$nickname = $senha = "";
$nickname_err = $password_err = $login_err = "";

// Processa dados do formulário quando ele é enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida o campo de nome de usuário
    if (empty(trim($_POST["nickname"]))) {
        $nickname_err = "Por favor, insira o nome de usuário.";
    } else {
        $nickname = trim($_POST["nickname"]);
    }

    // Valida o campo de senha
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, insira sua senha.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Se não houver erros nos campos de nome de usuário e senha
    if (empty($nickname_err) && empty($password_err)) {
        $sql_func = "SELECT idfunc, username, senha, funcao FROM funcionarios WHERE username = ?";
        $stmt_func = null;
        $stmt_user = null;

        // Tenta preparar e executar a instrução para funcionários
        if ($stmt_func = mysqli_prepare($conexao, $sql_func)) {
            mysqli_stmt_bind_param($stmt_func, "s", $param_nickname);
            $param_nickname = $nickname;
            if (mysqli_stmt_execute($stmt_func)) {
                mysqli_stmt_store_result($stmt_func);

                if (mysqli_stmt_num_rows($stmt_func) == 1) {
                    mysqli_stmt_bind_result($stmt_func, $id, $nickname, $hashed_password, $funcao);
                    if (mysqli_stmt_fetch($stmt_func)) {
                        if (verify_password($password, $hashed_password)) {
                            // Credenciais de funcionário são válidas, armazena na sessão
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nickname"] = $nickname;
                            $_SESSION["funcao"] = $funcao; // Armazena a função do funcionário

                            // Redireciona para a página principal de jogos
                            redirect("index.php");
                        } else {
                            $login_err = "Nome de usuário ou senha inválidos.";
                        }
                    }
                }
            } else {
                echo "Ops! Algo deu errado ao buscar funcionário. Por favor, tente novamente mais tarde. Erro: " . mysqli_error($conexao);
            }
            mysqli_stmt_close($stmt_func);
        } else {
            echo "Ops! Algo deu errado na preparação da query de funcionário. Erro: " . mysqli_error($conexao);
        }


                           
    }

    // Fecha a conexão com o banco de dados
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
        <?php
        // Exibe mensagem de erro de login, se houver
        if (!empty($login_err)) {
            echo '<div class="error-message text-center">' . $login_err . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

            <div class="inputBx">
                <input type="text" placeholder="nickname" name="nickname" value="<?php echo htmlspecialchars($nickname); ?>">
                <span class="error-message"><?php echo $nickname_err; ?></span>
            </div>

            <div class="inputBx">
                <input type="password" placeholder="Password" name="password">
                <span class="error-message"><?php echo $password_err; ?></span>
            </div>
            <div class="inputBx">
                <input type="submit" value="Entrar">
            </div>
        </form>
    </div>
</div>
</body>
</html>
