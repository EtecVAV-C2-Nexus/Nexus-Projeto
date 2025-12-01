<?php 
// O código verifica se a ação e o parâmetro estão vazios e retorna erro se necessário.
if ($acao == '' && $param == '') { 
    echo json_encode(['ERRO' => 'Caminho não encontrado!']); 
    exit; 
}

if ($acao == 'adiciona' && $param == '') {
    // uma cópia de $_POST para manipular os dados antes da inserção.
    $dados_para_inserir = $_POST;

    // Verifica se o campo 'senha' existe e aplica o hash seguro.
    // Se o campo de senha tiver outro nome (ex: 'password'), mude 'senha' abaixo.
    if (isset($dados_para_inserir['senha'])) {
        $dados_para_inserir['senha'] = password_hash($dados_para_inserir['senha'], PASSWORD_DEFAULT);
    }

    // --- CONSTRUÇÃO DO PREPARED STATEMENT (MUITO MAIS SEGURO!) ---

    // Separa as chaves (colunas) e os valores.
    $colunas = array_keys($dados_para_inserir);
    $valores = array_values($dados_para_inserir);

    // Converte o array de colunas para uma string separada por vírgulas: "nome,email,senha"
    $colunas_sql = implode(',', $colunas);

    // Cria os placeholders '?' para cada coluna: "?,?,?"
    $placeholders = implode(',', array_fill(0, count($colunas), '?'));

    // Monta a query final.
    $sql = "INSERT INTO funcionarios ({$colunas_sql}) VALUES ({$placeholders})";
    
    // --- EXECUÇÃO ---
    
    $db = DB::connect();
    $rs = $db->prepare($sql);
    
    // Passa o array de valores ($valores) para o execute.
    // O PDO irá lidar com a sanitização e a substituição dos '?' de forma segura.
    $exec = $rs->execute($valores);

    if ($exec) {
        echo json_encode(["dados" => 'Dados foram inseridos com sucesso.']);
    } else {
        echo json_encode(["dados" => 'Houve algum erro ao inserir os dados.']);
    }
}
?>