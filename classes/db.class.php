<?php
class DB
{
    public static function connect()
    {
       $SERVERNAME = "localhost";
    $username = "root";
    $Password = "";
    $database = "Nexus";

        return new PDO("mysql:host={$SERVERNAME};dbname={$database};charset=UTF8;", $username, $Password);
    }
}