<?php
require_once 'database.php';
require_once 'funcoes.php';

if (!logado() || !gerente()) {
    redirect("login.php");
}

$id = $nome = $descr = $preço = $imagem_atual = $estoque = ""; 
$nome_err = $descr_err = $preco_err = $imagem_err = $estoque_err = ""; 
$success_message = "";

// --- GET: Carregar os dados atuais do jogo ---
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    $sql = "SELECT ID, nome, descr, preço, imagem, estoque FROM jogos WHERE ID = ?"; 
    if ($stmt = mysqli_prepare($conexao, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $id;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id_db, $nome_db, $descr_db, $preço_db, $imagem_db, $estoque_db);
                $jogo = mysqli_fetch_assoc($resultado);
                mysqli_stmt_fetch($stmt);

                $nome = $nome_db;
                $descr = $descr_db;
                $preço = $preço_db;
                $imagem_atual = $imagem_db;
                $estoque = $estoque_db; 

            } else {
                echo "Jogo não encontrado.";
                redirect("index.php");
                exit();
            }
        } else {
            echo "Ops! Algo deu errado ao buscar o jogo.";
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}
// --- POST: Atualizar dados do jogo ---
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nome = trim($_POST['nome'] ?? '');
    $descr = trim($_POST['descr'] ?? '');
    $preço = trim($_POST['preço'] ?? 0);
    $estoque = trim($_POST['estoque'] ?? 0);

     $imagem_atual = $_POST["imagem_atual"];
    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES["imagem"]["type"], $allowed_types)) {
            $imagem_atual = file_get_contents($_FILES["imagem"]["tmp_name"]); // BLOB
        } else {
            $imagem_err = "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
        }
    }


    if(!$nome || !$estoque){
        echo "preencha nome e/ou estoque";
    } else {
        $sql = "UPDATE jogos set nome = ?, descr = ?, preço = ?, imagem = ?, estoque = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        if($stmt){
            mysqli_stmt_bind_param($stmt, "sssibi", $nome, $descr, $preço, $imagem_atual, $estoque, $id);
            if(mysqli_stmt_execute($stmt)){
                
                $jogo = [
                'nome' => $nome,
                'descr' => $descr,
                'preço' => $preço,
                'imagem' => $imagem_atual
                'estoque' => $estoque,
                
                ];

                 
            } else {
                echo "erro ao atualizar: " . mysqli_error($conexao);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "erro ao preparar atualização";
        }

    }
}
   

mysqli_close($conexao);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Jogo - Nexus</title>
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
        .wrapper .inputBx {
            position: relative;
            width: 100%;
            margin-bottom: 25px;
        }
        .wrapper .inputBx input,
        .wrapper .inputBx textarea {
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
        .wrapper .inputBx input:focus,
        .wrapper .inputBx textarea:focus {
            border-color: #35ff90;
            box-shadow: 0 0 8px rgba(53, 255, 144, 0.5);
        }
        .wrapper .inputBx input[type="file"] {
            padding: 5px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }
        .wrapper .inputBx input[type="submit"] {
            background: linear-gradient(45deg, #247BA0, #2c0597);
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .wrapper .inputBx input[type="submit"]:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .wrapper .error-message {
            color: #FF6347;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
            text-align: left;
        }
        .wrapper .success-message {
            color: #69B578;
            font-size: 1em;
            margin-bottom: 20px;
            display: block;
            text-align: center;
        }
        .wrapper .links a {
            color: #35ff90;
            text-decoration: none;
            font-size: 0.9em;
            margin: 0 10px;
            transition: 0.3s ease;
        }
        .wrapper .links a:hover {
            text-decoration: underline;
        }
        .current-image-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            object-fit: cover;
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
                <h2>Editar Jogo (Produto)</h2>
                <?php
                if (!empty($success_message)) {
                    echo '<div class="success-message">' . $success_message . '</div>';
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $id; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($imagem_atual); ?>">

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
                        <span class="error-message"><?php echo $preco_err; ?></span>
                    </div>

                    <div class="inputBx">
                        <label for="estoque" style="color: #fff; font-size: 0.9em; margin-bottom: 5px; display: block; text-align: left;">Estoque:</label>
                        <input type="number" placeholder="Quantidade em Estoque" name="estoque" id="estoque" value="<?php echo htmlspecialchars($estoque); ?>" min="0">
                        <span class="error-message"><?php echo $estoque_err; ?></span>
                    </div>

                    <div class="inputBx">
                        <label for="imagem" style="color: #fff; font-size: 0.9em; margin-bottom: 5px; display: block; text-align: left;">Imagem do Jogo:</label>
                        <?php if (!empty($imagem_atual)): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($imagem_atual); ?>" alt="Imagem atual do jogo" class="current-image-preview">
                            <p style="font-size: 0.8em; color: #ccc;">Imagem atual</p>
                        <?php endif; ?>
                        <input type="file" name="imagem" id="imagem" accept="image/jpeg,image/png,image/gif">
                        <span class="error-message"><?php echo $imagem_err; ?></span>
                    </div>

                    <div class="inputBx">
                        <input type="submit" value="Salvar Alterações">
                    </div>
                    <div class="links">
                        <a href="index.php">Voltar para o Catálogo</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

