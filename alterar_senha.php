<?php
session_start();

require_once 'database.php';
require_once 'funcoes.php';

if (!logado()) {
    redirect("login.php");
}

$user_id = $_SESSION["id"];

$current_password = $new_password = $confirm_new_password = "";
$current_password_err = $new_password_err = $confirm_new_password_err = "";
$password_change_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["current_password"]))) {
        $current_password_err = "Por favor, insira sua senha atual.";
    } else {
        $current_password = trim($_POST["current_password"]);
    }

    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Por favor, insira uma nova senha.";
    } elseif (!validate_password(trim($_POST["new_password"]))) {
        $new_password_err = "A nova senha deve ter entre 8 e 16 caracteres, e conter letras maiúsculas, minúsculas e números (sem caracteres especiais).";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Valida a confirmação da nova senha
    if (empty(trim($_POST["confirm_new_password"]))) {
        $confirm_new_password_err = "Por favor, confirme a nova senha.";
    } else {
        $confirm_new_password = trim($_POST["confirm_new_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_new_password)) {
            $confirm_new_password_err = "As novas senhas não coincidem.";
        }
    }

    if (empty($current_password_err) && empty($new_password_err) && empty($confirm_new_password_err)) {
        $hashed_password_db = '';

        $table_name = 'funcionarios';
        $id_column = 'idfunc';
        $password_column = 'senha';

        $sql_select_password = "SELECT " . $password_column . " FROM " . $table_name . " WHERE " . $id_column . " = ?";
        if ($stmt = mysqli_prepare($conexao, $sql_select_password)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id); 
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $hashed_password_db);
                    mysqli_stmt_fetch($stmt);

                    // Verifica se a senha atual fornecida corresponde ao hash armazenado
                    if (verify_password($current_password, $hashed_password_db)) {
                        $sql_update_password = "UPDATE " . $table_name . " SET " . $password_column . " = ? WHERE " . $id_column . " = ?";
                        if ($stmt_update = mysqli_prepare($conexao, $sql_update_password)) {
                            $param_new_password = hash_password($new_password); 
                            mysqli_stmt_bind_param($stmt_update, "si", $param_new_password, $user_id);
                            if (mysqli_stmt_execute($stmt_update)) {
                                $password_change_success = "Sua senha foi alterada com sucesso!";
                                // Limpa os campos do formulário após o sucesso
                                $current_password = $new_password = $confirm_new_password = "";
                            } else {
                                error_log("Erro ao atualizar senha no DB (" . $table_name . "): " . mysqli_error($conexao));
                                echo "Ops! Algo deu errado ao tentar atualizar a senha. Por favor, tente novamente mais tarde.";
                            }
                            mysqli_stmt_close($stmt_update);
                        } else {
                            error_log("Erro ao preparar update statement (" . $table_name . "): " . mysqli_error($conexao));
                            echo "Ops! Algo deu errado ao preparar a atualização da senha. Por favor, tente novamente mais tarde.";
                        }
                    } else {
                        $current_password_err = "A senha atual está incorreta.";
                    }
                } else {
                    error_log("Dados do usuário/funcionário não encontrados para ID: " . $user_id . " na tabela " . $table_name);
                    echo "Ops! Dados do usuário não encontrados. Por favor, tente novamente mais tarde.";
                }
            } else {
                error_log("Erro ao executar select statement (" . $table_name . "): " . mysqli_error($conexao));
                echo "Ops! Algo deu errado ao buscar os dados da senha. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("Erro ao preparar select statement (" . $table_name . "): " . mysqli_error($conexao));
            echo "Ops! Algo deu errado ao preparar a consulta de senha. Por favor, tente novamente mais tarde.";
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
    <title>Alterar Senha - Nexus</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        .wrapper {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: #fff;
            max-width: 400px;
            margin: 50px auto;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.5);
            backdrop-filter: blur(5px);
            border: 2px solid #35ff90;
            position: relative;
            z-index: 1000;
        }
        .wrapper h2 {
            font-size: 2.2em;
            margin-bottom: 20px;
            color: #35ff90;
            text-shadow: 0 0 10px #35ff90;
        }
        .inputBx { 
            position: relative;
            width: 100%;
            margin-bottom: 25px;
        }
        .inputBx input[type="password"] {
            width: calc(100% - 20px); 
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            outline: none;
            color: #fff;
            font-size: 1em;
            transition: 0.3s ease;
        }
        .inputBx input[type="password"]:focus {
            border-color: #35ff90;
            box-shadow: 0 0 8px rgba(53, 255, 144, 0.5);
        }
        .wrapper input[type="submit"] { 
            display: inline-block;
            background: linear-gradient(45deg, #247BA0, #2c0597);
            color: #fff;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            margin-top: 20px;
            border: none; 
            cursor: pointer;
        }

        .wrapper input[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
            opacity: 0.9;
        }
        .error-message { 
            color: #FF6347;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
            text-align: left;
        }
        .success-message { 
            color: #69B578;
            font-size: 1em;
            margin-bottom: 20px;
            display: block;
            text-align: center;
        }
        .links {
            margin-top: 15px;
        }
        .links a {
            color: #35ff90;
            text-decoration: none;
            font-size: 0.9em;
            transition: 0.3s ease;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="anel">
        <i style="--clr:#69B578;"></i>
        <i style="--clr:#247BA0;"></i>
        <i style="--clr:#2c0597;"></i>
        <div class="login"> 
            <div class="wrapper">
                <h2>Alterar Senha</h2>
                <?php
                if (!empty($password_change_success)) {
                    echo '<div class="success-message">' . $password_change_success . '</div>';
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="inputBx">
                        <input type="password" placeholder="Senha Atual" name="current_password" value="<?php echo htmlspecialchars($current_password); ?>">
                        <span class="error-message"><?php echo $current_password_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="password" placeholder="Nova Senha" name="new_password" value="<?php echo htmlspecialchars($new_password); ?>">
                        <span class="error-message"><?php echo $new_password_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="password" placeholder="Confirmar Nova Senha" name="confirm_new_password" value="<?php echo htmlspecialchars($confirm_new_password); ?>">
                        <span class="error-message"><?php echo $confirm_new_password_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="submit" value="Alterar Senha">
                    </div>
                </form>
                <div class="links">
                    <a href="perfil.php">Voltar para o Perfil</a>
                    <a href="index.php">Voltar para o Catálogo</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
