<?php 
$SERVERNAME = "localhost";
$username = "root";
$Password = "";
$database = "Nexus";

$conexao = mysqli_connect($SERVERNAME, $username, $Password, $database);

$sql ="SELECT * FROM `jogos`";
$resultado = mysqli_query($conexao, $sql);
if (!$conexao) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}


?>




