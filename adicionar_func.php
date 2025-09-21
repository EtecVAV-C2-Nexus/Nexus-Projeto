<?php
session_start();

require_once 'database.php';
require_once 'funcoes.php';

if (!logado() || !gerente()) {
    redirect("login.php");
}

$username = $password = $confirm_password = $funcao = $nome_completo = $email = "";
$username_err = $password_err = $confirm_password_err = $funcao_err = $nome_completo_err = $email_err = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida o nome de usuário (username) do funcionário
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor, insira o nome de usuário do funcionário.";
    } elseif (!validate_nickname(trim($_POST["username"]))) {
        $username_err = "O nome de usuário pode conter apenas letras, números e underscores, entre 3 e 50 caracteres.";
    } else {
        $sql = "SELECT idfunc FROM funcionarios WHERE username = ?";
        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "Este nome de usuário já está em uso.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Valida o nome completo
    if (empty(trim($_POST["nome_completo"]))) {
        $nome_completo_err = "Por favor, insira o nome completo do funcionário.";
    } else {
        $nome_completo = trim($_POST["nome_completo"]);
        if (!preg_match("/^[a-zA-ZáàâãéèêíïóôõöúüçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÜÇÑ' -]+$/", $nome_completo)) {
            $nome_completo_err = "O nome completo pode conter apenas letras e espaços.";
        }
    }

    // Valida o email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, insira o e-mail do funcionário.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Por favor, insira um endereço de e-mail válido.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Valida a senha
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, insira a senha.";
    } elseif (!validate_password(trim($_POST["password"]))) { // Usa a função de validação de senha
        $password_err = "A senha deve ter entre 8 e 16 caracteres, e conter letras maiúsculas, minúsculas e números (sem caracteres especiais).";
    } else {
        $password = trim($_POST["password"]);
    }

    // Valida a confirmação da senha
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Por favor, confirme a senha.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "As senhas não coincidem.";
        }
    }

   
    // Valida a função
if (empty(trim($_POST["funcao"]))) {
    $funcao_err = "Por favor, selecione uma função para o funcionário.";
} else {
    $funcao = trim($_POST["funcao"]);
    $allowed_functions = ['gerente', 'repositor', 'funcionario']; // Adicionado 'funcionario'
    if (!in_array($funcao, $allowed_functions)) {
        $funcao_err = "Função inválida selecionada.";
    }
}

    }


    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($funcao_err) && empty($nome_completo_err) && empty($email_err)) {
        $sql = "INSERT INTO funcionarios (username, nome_completo, email, senha, funcao) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssss", $param_username, $param_nome_completo, $param_email, $param_senha, $param_funcao);

            $param_username = $username;
            $param_nome_completo = $nome_completo;
            $param_email = $email;
            $param_senha = hash_password($password); 
            $param_funcao = $funcao;

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Funcionário '" . htmlspecialchars($username) . "' adicionado com sucesso!";
                $username = $password = $confirm_password = $funcao = $nome_completo = $email = "";
            } else {
                echo "Ops! Algo deu errado ao adicionar o funcionário: " . mysqli_error($conexao) . ". Por favor, tente novamente mais tarde.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Ops! Algo deu errado ao preparar a declaração: " . mysqli_error($conexao) . ". Por favor, tente novamente mais tarde.";
        }
    }

    mysqli_close($conexao);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Funcionário - Nexus</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        .wrapper {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: #fff;
            max-width: 500px;
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
        option{
            color:#000;
        }
        .inputBx {
            position: relative;
            width: 100%;
            margin-bottom: 25px;
        }
        .inputBx input[type="text"],
        .inputBx input[type="password"],
        .inputBx select {
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
        .inputBx input:focus,
        .inputBx select:focus {
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
             color: #fff;
            text-decoration: none;
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .links a:hover {
             background-color: #247BA0;
            color:rgb(224, 224, 224);
        }
        .inputBx label {s
            color: #fff;
            font-size: 0.9em;
            margin-bottom: 5px;
            display: block;
            text-align: left;
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
                <h2>Adicionar Funcionário</h2>
                <?php
                if (!empty($success_message)) {
                    echo '<div class="success-message">' . $success_message . '</div>';
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="inputBx">
                        <input type="text" placeholder="Nome de Usuário" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        <span class="error-message"><?php echo $username_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="text" placeholder="Nome Completo" name="nome_completo" value="<?php echo htmlspecialchars($nome_completo); ?>">
                        <span class="error-message"><?php echo $nome_completo_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="text" placeholder="E-mail" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <span class="error-message"><?php echo $email_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="password" placeholder="Senha" name="password" value="<?php echo htmlspecialchars($password); ?>">
                        <span class="error-message"><?php echo $password_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="password" placeholder="Confirmar Senha" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>">
                        <span class="error-message"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <label for="funcao">Função:</label>
                        <select name="funcao" id="funcao">
                            <option value="">-- Selecione uma função --</option>
                            <option value="gerente" <?php echo ($funcao === 'gerente') ? 'selected' : ''; ?>>Gerente</option>
                            <option value="repositor" <?php echo ($funcao === 'repositor') ? 'selected' : ''; ?>>Repositor</option>
                            <option value="funcionario" <?php echo ($funcao === 'funcionario') ? 'selected' : ''; ?>>Funcionario</option>
                        </select>
                        <span class="error-message"><?php echo $funcao_err; ?></span>
                    </div>
                    <div class="inputBx">
                        <input type="submit" value="Adicionar Funcionário">
                    </div>
                    <div class="links">
                        <a href="index.php">Voltar para o Catálogo</a>
                        <a href="funcionarios.php">Voltar para lista de funcionarios</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
