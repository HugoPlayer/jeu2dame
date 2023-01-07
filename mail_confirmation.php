<?php
session_start();
require('./db.php');

if(isset($_GET['mail'], $_GET['key']) AND !empty($_GET['mail']) AND !empty($_GET['key'])) {
    if(isset($_GET['origin']) AND !empty($_GET['origin']) AND $_GET['origin'] == "create"){
        $mail = htmlspecialchars(urldecode($_GET['mail']));
        $key = htmlspecialchars(urldecode($_GET['key']));
        $requser = $db->prepare("SELECT * FROM membres WHERE mail = ? AND confirmkey = ?");
        $requser->execute(array($mail, $key));
        $userexist = $requser->rowCount();
        if($userexist) {
            $user = $requser->fetch();
            if($user['mail_confirm'] == 0) {
                $updateuser = $db->prepare("UPDATE membres SET mail_confirm = 1 WHERE mail = ? AND confirmkey = ?");
                $updateuser->execute(array($mail,$key));
                echo "Votre compte a bien été confirmé !";
            } else {
                echo "Votre compte a déjà été confirmé !";
            }
        } else {
            echo "L'utilisateur n'existe pas !";
        }
    } elseif(isset($_GET['origin']) AND !empty($_GET['origin']) AND $_GET['origin'] == "edit"){
        $mail = htmlspecialchars(urldecode($_GET['mail']));
        $key = htmlspecialchars(urldecode($_GET['key']));
        // valider_user1=1 + modifier la colonne mail 
        $req = $db->prepare("SELECT * FROM requete_attente WHERE newmail = ? AND mailconfirm = ?");
        $req->execute(array($mail, $key));
        $requete_exist = $req->rowCount();
        if($requete_exist){
            $requete = $req->fetch();
            if($requete['valider_user1'] == NULL){
                $req_update = $db->prepare("UPDATE requete_attente SET valider_user1 = 1 WHERE id = ?");
                $req_update->execute(array($requete['id']));
                $req_update2 = $db->prepare("UPDATE membres SET mail = ? WHERE id = ?");
                $req_update2->execute(array($mail, $requete['id_joueur1']));
                echo "Votre adresse mail a bien été validée !";
            } else {
                echo "Votre adresse mail a déjà été validée !";
            }
        }
    }
}

?>