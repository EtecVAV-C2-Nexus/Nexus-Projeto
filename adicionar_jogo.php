<?php
session_start();

require_once 'database.php';
require_once 'funcoes.php';

if (!logado() || !gerente()) {
    redirect("login.php"); 
}

$nome = $descr = $preço = "";
$nome_err = $descr_err = $preço_err = $imagem_err = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida o nome do jogo
    if (empty(trim($_POST["nome"]))) {
        $nome_err = "Por favor, insira o nome do jogo.";
    } else {
        $nome = trim($_POST["nome"]);
    }

    // Valida a descrição
    $descr = trim($_POST["descr"]);
    if (strlen($descr) > 500) {
        $descr_err = "A descrição não pode ter mais de 500 caracteres.";
    }

    // Valida o preço
    if (empty(trim($_POST["preço"]))) {
        $preço_err = "Por favor, insira o preço do jogo.";
    } elseif (!is_numeric(trim($_POST["preço"])) || trim($_POST["preço"]) < 0) {
        $preço_err = "Por favor, insira um preço válido.";
    } else {
        $preço = number_format(trim($_POST["preço"]), 2, '.', ''); 
    }

    // Upload da imagem
    $imagem_data = null;
    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_info = pathinfo($_FILES["imagem"]["name"]);
        $file_extension = strtolower($file_info['extension']);

        if (in_array($file_extension, $allowed_types)) {
            // Lê o conteúdo do arquivo de imagem
            $imagem_data = file_get_contents($_FILES["imagem"]["tmp_name"]);
            // Garante que o tamanho da imagem não exceda o limite do BLOB 
            if (strlen($imagem_data) > (16 * 1024 * 1024)) {
                $imagem_err = "A imagem é muito grande (máximo 16MB).";
                $imagem_data = null;
            }
        } else {
            $imagem_err = "Formato de arquivo de imagem inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.";
        }
    } else {
        $imagem_err = "Por favor, selecione uma imagem para o jogo.";
    }


    if (empty($nome_err) && empty($descr_err) && empty($preço_err) && empty($imagem_err)) {
        $sql = "INSERT INTO jogos (nome, descr, preço, imagem) VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssds", $param_nome, $param_descr, $param_preço, $param_imagem);

            $param_nome = $nome;
            $param_descr = $descr;
            $param_preço = $preço;
            $param_imagem = $imagem_data;

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Jogo adicionado com sucesso!";
                // Limpa os campos do formulário após o sucesso
                $nome = $descr = $preço = "";
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
            } else {
                echo "Ops! Algo deu errado ao adicionar o jogo. Por favor, tente novamente mais tarde. Erro: " . mysqli_error($conexao);
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Ops! Algo deu errado na preparação da query. Erro: " . mysqli_error($conexao);
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
    <title>Adicionar Jogo - Nexus</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        body {
            background: #1a1a2e; 
            color: #e0e0e0;
            font-family: 'Inter', sans-serif;
            display: flex; 
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .anel {
            position: relative; 
            width: 500px;
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login { 
            position: absolute;
            width: 350px; 
            height: auto; 
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 20px;
            background-color: rgba(0, 0, 0, 0.7); 
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.5); 
            backdrop-filter: blur(5px);
            border: 2px solid #35ff90; 
            z-index: 10;
        }

        .login h2 {
            font-size: 2.2em;
            color: #35ff90; 
            text-shadow: 0 0 10px #35ff90;
            margin-bottom: 20px;
        }

        .login .inputBx {
            position: relative;
            width: 90%; 
        }

        .login .inputBx input[type="text"],
        .login .inputBx input[type="number"],
        .login .inputBx textarea {
            width: 100%;
            padding: 12px 20px;
            background: transparent;
            border: 2px solid #fff;
            border-radius: 40px;
            font-size: 1.1em;
            color: #fff;
            box-shadow: none;
            outline: none;
            box-sizing: border-box; 
        }

        .login .inputBx textarea {
            border-radius: 15px; 
            min-height: 80px;
            resize: vertical; 
        }

        .login .inputBx input[type="file"] {
            padding: 10px;
            border: 2px solid #fff;
            border-radius: 40px;
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .login .inputBx input[type="submit"] {
            width: 100%;
            background: linear-gradient(45deg, #35ff90, #2c0597);
            border: none;
            cursor: pointer;
            padding: 12px 20px;
            border-radius: 40px;
            font-size: 1.2em;
            color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .login .inputBx input[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
            opacity: 0.9;
        }

        .login .inputBx input::placeholder,
        .login .inputBx textarea::placeholder {
            color: rgba(255, 255, 255, 0.75);
        }

        .error-message {
            color: #ff4d4d; 
            font-size: 0.9em;
            margin-top: 5px;
            display: block; 
        }

        .success-message {
            color: #35ff90; 
            font-size: 1em;
            margin-bottom: 20px;
            text-align: center;
        }

        .links a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #35ff90;
        }
    </style>
</head>
<body>
<div class="anel">
  <i style="--clr:#69B578;"></i>
  <i style="--clr:#247BA0;"></i>
  <i style="--clr:#2c0597;"></i>
  <div class="login">
        <h2>Adicionar Novo Jogo</h2>
        <?php
        if (!empty($success_message)) {
            echo '<div class="success-message">' . $success_message . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="inputBx">
                <input type="text" placeholder="Nome do Jogo" name="nome" value="<?php echo htmlspecialchars($nome); ?>">
                <span class="error-message"><?php echo $nome_err; ?></span>
            </div>

            <div class="inputBx">
                <textarea placeholder="Descrição do Jogo" name="descr"><?php echo htmlspecialchars($descr); ?></textarea>
                <span class="error-message"><?php echo $descr_err; ?></span>
            </div>

            <div class="inputBx">
                <input type="number" step="0.01" placeholder="Preço (ex: 59.99)" name="preço" value="<?php echo htmlspecialchars($preço); ?>">
                <span class="error-message"><?php echo $preço_err; ?></span>
            </div>

            <div class="inputBx">
                <label for="imagem" style="color: #fff; font-size: 0.9em; margin-bottom: 5px; display: block;">Imagem do Jogo:</label>
                <input type="file" name="imagem" id="imagem" accept="image/jpeg,image/png,image/gif">
                <span class="error-message"><?php echo $imagem_err; ?></span>
            </div>

            <div class="inputBx">
                <input type="submit" value="Adicionar Jogo">
            </div>
            <div class="links">
                <a href="index.php">Voltar para o Catálogo</a>
                <a href="perfil.php">Meu Perfil</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
