<?php
session_start();
require_once 'database.php';
require_once 'funcoes.php';

if (!logado() || !gerente()) {
    redirect("login.php");
}

$id = $nome = $descr = $preco = $imagem_atual = $estoque = "";
$nome_err = $descr_err = $preco_err = $imagem_err = $estoque_err = "";
$success_message = "";

// --- GET: Carregar dados do jogo ---
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    $sql = "SELECT ID, nome, descr, `preço`, imagem, estoque FROM jogos WHERE ID = ?";
    if ($stmt = mysqli_prepare($conexao, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id_db, $nome_db, $descr_db, $preco_db, $imagem_db, $estoque_db);
            mysqli_stmt_fetch($stmt);

            $nome = $nome_db;
            $descr = $descr_db;
            $preco = $preco_db;
            $imagem_atual = $imagem_db;
            $estoque = $estoque_db;
        } else {
            redirect("index.php");
        }
        mysqli_stmt_close($stmt);
    }
}

// --- POST: Atualizar dados ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descr = trim($_POST['descr'] ?? '');
    $preco = trim($_POST['preco'] ?? 0);
    $estoque = trim($_POST['estoque'] ?? 0);
    $id = trim($_POST['id'] ?? '');

    // Validações
    if (empty($nome)) $nome_err = "Preencha o nome do jogo.";
    if ($estoque === '') $estoque_err = "Preencha a quantidade em estoque.";

    $nova_imagem = null; // Variável para armazenar a nova imagem, se houver
    $update_imagem = false; // Flag para indicar se a imagem precisa ser atualizada

    // Verifica se uma nova imagem foi enviada
    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES["imagem"]["type"], $allowed_types)) {
            $nova_imagem = file_get_contents($_FILES["imagem"]["tmp_name"]);
            $update_imagem = true; // Define a flag para atualizar a imagem
        } else {
            $imagem_err = "Apenas JPG, PNG e GIF são permitidos.";
        }
    }

    if (empty($nome_err) && empty($estoque_err) && empty($imagem_err)) {
        // Constrói a query SQL dinamicamente
        if ($update_imagem) {
            // Atualiza o jogo COM a nova imagem
            $sql = "UPDATE jogos SET nome=?, descr=?, `preço`=?, imagem=?, estoque=? WHERE ID=?";
            $stmt = mysqli_prepare($conexao, $sql);
            $null = null;
            mysqli_stmt_bind_param($stmt, "ssdbii", $nome, $descr, $preco, $null, $estoque, $id);
            mysqli_stmt_send_long_data($stmt, 3, $nova_imagem);
        } else {
            // Atualiza o jogo SEM a imagem (mantém a imagem existente)
            $sql = "UPDATE jogos SET nome=?, descr=?, `preço`=?, estoque=? WHERE ID=?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "ssdii", $nome, $descr, $preco, $estoque, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Jogo atualizado com sucesso!";
            // Se a imagem foi atualizada, a gente precisa recarregar o valor da imagem_atual
            if ($update_imagem) {
                $imagem_atual = $nova_imagem;
            }
        } else {
            echo "Erro ao atualizar: " . mysqli_error($conexao);
        }
        mysqli_stmt_close($stmt);
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
<style>
/* CSS do seu código original */
body{margin:0;padding:0;font-family:Inter,sans-serif;background:#111;color:#fff;display:flex;justify-content:center;align-items:center;min-height:100vh}
.anel{display:flex;justify-content:center;align-items:center;width:100%}
.wrapper{background:rgba(0,0,0,.8);padding:30px;border-radius:10px;max-width:500px;width:90%;box-shadow:0 0 20px rgba(0,255,144,.5);border:2px solid #35ff90}
.wrapper h2{text-align:center;margin-bottom:20px;color:#35ff90;text-shadow:0 0 10px #35ff90}
.inputBx{margin-bottom:20px;display:flex;flex-direction:column}
.inputBx input,.inputBx textarea{padding:10px;border-radius:5px;border:1px solid rgba(255,255,255,.3);background:rgba(255,255,255,.05);color:#fff;font-size:1em;outline:none}
.inputBx input:focus,.inputBx textarea:focus{border-color:#35ff90;box-shadow:0 0 8px rgba(53,255,144,.5)}
.inputBx input[type="submit"]{cursor:pointer;background:linear-gradient(45deg,#247BA0,#2c0597);border:none;padding:12px 25px;border-radius:30px;font-weight:600;transition:.3s}
.inputBx input[type="submit"]:hover{opacity:.9;transform:translateY(-2px)}
.error-message{color:#FF6347;font-size:.85em;margin-top:5px}
.success-message{color:#69B578;text-align:center;margin-bottom:15px}
.current-image-preview{max-width:150px;max-height:150px;margin-top:10px;border-radius:5px;object-fit:cover;border:1px solid rgba(255,255,255,.3)}
.links{text-align:center;margin-top:15px}
.links a{color:#35ff90;text-decoration:none;font-size:.9em}
.links a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="anel">
    <div class="wrapper">
        <h2>Editar Jogo (Produto)</h2>
        <?php if(!empty($success_message)) echo "<div class='success-message'>$success_message</div>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])."?id=".$id;?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id);?>">
            <div class="inputBx">
                <p>Nome do jogo</p>
                <input type="text" name="nome" placeholder="Nome do Jogo" value="<?php echo htmlspecialchars($nome);?>">
                <span class="error-message"><?php echo $nome_err;?></span>
            </div>

            <div class="inputBx">
                <p>Descrição</p>
                <textarea name="descr" placeholder="Descrição do Jogo"><?php echo htmlspecialchars($descr);?></textarea>
                <span class="error-message"><?php echo $descr_err;?></span>
            </div>

            <div class="inputBx">
                <p>Preço</p>
                <input type="number" step="0.01" name="preco" placeholder="Preço (ex: 59.99)" value="<?php echo htmlspecialchars($preco);?>">
                <span class="error-message"><?php echo $preco_err;?></span>
            </div>

            <div class="inputBx">
                <p>Estoque</p>
                <input type="number" name="estoque" placeholder="Quantidade em Estoque" min="0" value="<?php echo htmlspecialchars($estoque);?>">
                <span class="error-message"><?php echo $estoque_err;?></span>
            </div>

            <div class="inputBx">
                <p>Imagem</p>
                <?php 
                if(!empty($imagem_atual)) {
                    echo '<img src="data:image/jpeg;base64,'.base64_encode($imagem_atual).'" class="current-image-preview" alt="Imagem atual">';
                    echo '<p style="font-size:0.8em;color:#ccc;">Imagem atual (não precisa enviar outra para manter esta)</p>';
                } else {
                    echo '<img src="fallback.png" class="current-image-preview" alt="Sem imagem">';
                    echo '<p style="font-size:0.8em;color:#ccc;">Sem imagem</p>';
                }
                ?>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/gif">
                <span class="error-message"><?php echo $imagem_err;?></span>
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
</body>
</html>