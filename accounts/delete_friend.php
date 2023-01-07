<?php
session_start();
require('../db.php');

if(isset($_GET['id']) AND $_GET['id'] > 0) {
    $getid = intval($_GET['id']);
    if(isset($_SESSION['id'])) {
        $req = $db->prepare('SELECT * FROM liste_ami WHERE id = ?');
        $req->execute(array($getid));
        $ligne = $req->fetch();
        if($_SESSION['id'] == $ligne['id_joueur1']) {
            $req_update = $db->prepare('DELETE FROM requete_attente WHERE type = ? AND id_joueur1 = ? AND id_joueur2 = ?');
        } elseif($_SESSION['id'] == $ligne['id_joueur2']) {
            $req_update = $db->prepare('DELETE FROM requete_attente WHERE type = ? AND id_joueur2 = ? AND id_joueur1 = ?');
        } else {
            header("Location: profil");
        }
        $req_update->execute(array(1, $_SESSION['id'], $ligne['id_joueur2']));
        $req_delete = $db->prepare('DELETE FROM liste_ami WHERE id = ? AND (id_joueur1 = ? OR id_joueur2 = ?)');
        $req_delete->execute(array($getid, $_SESSION['id'], $_SESSION['id']));
        header("Location: profil");
    } else {
        header("Location: profil");
    }
} else {
    header("Location: profil");
}

?>