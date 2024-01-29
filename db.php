<?php
try{
    $db = new PDO('mysql:host=localhost;dbname='[DB NAME]', '[USERNAME DB]', '[PASSWORD]');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Echec de connexion : ".$e->getMessage();
}
?>
