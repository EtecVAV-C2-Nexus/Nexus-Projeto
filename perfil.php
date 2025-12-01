<?php
session_start();

require_once 'funcoes.php';

if (!logado()) {
    redirect("login.php");
}

if (isset($conexao)) { 
    mysqli_close($conexao); 
}

// Obtém o nome de usuário e a função da sessão
$nickname = htmlspecialchars($_SESSION["nickname"]);
$funcao = isset($_SESSION["funcao"]) ? htmlspecialchars($_SESSION["funcao"]) : "usuário comum";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Nexus</title>
    <link rel="stylesheet" href="css/estilo.css">
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
            z-index: 10;
        }

        .wrapper h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #35ff90; 
            text-shadow: 0 0 10px #35ff90;
        }

        .wrapper p {
            font-size: 1.1em;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .wrapper .btn {
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
        }

        .wrapper .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
            opacity: 0.9;
        }

        .profile-info {
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 20px;
        }

        .profile-info strong {
            color: #69B578;
        }
    </style>
</head>
<body>
    <div class="anel">
        <i style="--clr:#69B578;"></i>
        <i style="--clr:#247BA0;"></i>
        <i style="--clr:#2c0597;"></i>
        <div class="wrapper">
            <h2>Bem-vindo, <?php echo $nickname; ?>!</h2>
            <p>Você está logado na sua conta Nexus.</p>
            <div class="profile-info">
                <?php if (strtolower($funcao) !== "cliente") : ?>
                    <p>Sua função: <strong><?php echo $funcao; ?></strong></p>
                <?php endif; ?>
                <p>Aqui você pode ver informações do seu perfil.</p>
            </div>
            <a href="index.php" class="btn">Catálogo</a>
            <a href="logout.php" class="btn">Sair da Conta</a>
            <a href="meus_jogos.php" class="btn">Meus Jogos</a>
            <a href="alterar_senha.php" class="btn">Alterar senha</a>
        </div>
    </div>
</body>
</html>
