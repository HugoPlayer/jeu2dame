<?php
session_start();
require(dirname(__FILE__).'/vendor/autoload.php');

try{
    $db = new PDO('mysql:host=localhost;dbname=id18636344_jeu2dame', 'id18636344_webmaster', 'sxXp4ZD#C)]N!YZY');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Echec de connexion : ".$e->getMessage();
}