<?php
session_start();
require('../db.php');
 
if(isset($_SESSION['id'])) {
    $req = $db->prepare("SELECT * FROM membres WHERE id = ?");
    $req->execute(array($_SESSION['id']));
    $user = $req->fetch();
    if(isset($_POST['newusername']) AND !empty($_POST['newusername']) AND $_POST['newusername'] != $user['username']) {
        $newusername = htmlspecialchars($_POST['newusername']);
        if(strlen($newusername) <= 20) {
            $requsername = $db->prepare("SELECT * FROM membres WHERE username = ?");
            $requsername->execute(array($newusername));
            $usernameexist = $requsername->rowCount();
            if($usernameexist == 0) {
                $insertusername = $db->prepare("UPDATE membres SET username = ? WHERE id = ?");
                $insertusername->execute(array($newusername, $_SESSION['id']));
                $_SESSION['username'] = $newusername;
                $erreur = "Le nom d'utilisateur a bien été modifié.";
            } else {
                $erreur = "Ce nom d'utilisateur existe déjà.";
            }
        } else {
            $erreur = "Le nom d'utilisateur doit être inférieur à 20 caractères.";
        }
    }
    if(isset($_POST['newmail']) AND !empty($_POST['newmail']) AND $_POST['newmail'] != $user['mail']) {
        $newmail = htmlspecialchars($_POST['newmail']);
        if(filter_var($newmail, FILTER_VALIDATE_EMAIL)) {
            if(strlen($newmail <= 150)) {
                $reqmail = $db->prepare("SELECT * FROM membres WHERE mail = ?");
                $reqmail->execute(array($newmail));
                $mailexist = $reqmail->rowCount();
                if($mailexist == 0){
                    $req = $db->prepare("SELECT * FROM requete_attente WHERE type = ? AND id_joueur1 = ? AND newmail IS NOT NULL");
                    $req->execute(array(2, $_SESSION['id']));
                    $requete_exist = $req->rowCount();
                    $requete = $req->fetch();
                    if($requete_exist == 0 OR ($requete_exist == 1 AND $requete['valider_user1'] == 1)){
                        $longueurKey = 40;
                        $key = "";
                        for($i=1;$i<$longueurKey;$i++) {
                            $key .= mt_rand(0,9);
                        }
                        $req_newmail = $db->prepare("INSERT INTO requete_attente(type, id_joueur1, newmail, mailconfirm) VALUES (?,?,?,?)");
                        $req_newmail->execute(array(2, $_SESSION['id'], $newmail, $key));
                        $header="MIME-Version: 1.0\r\n";
                        $header.='From:"[Jeu2Dame]"<info@jeu2dame.tk>'."\n";
                        $header.='Content-Type:text/html; charset="utf-8"'."\n";
                        $header.='Content-Transfer-Encoding: 8bit';
                        $message='
                        <html>
                        <head>
                        <meta charset="utf-8">
                        </head>
                        <body>
                        <div align="center">
                        <h1>Confirmez la modification de l\'adresse mail vers '.$newmail.'<br/>Cliquez sur le lien si dessous pour confirmer</h1>
                        <a href="http://jeu2dame.tk/mail_confirmation?origin=edit&mail='.urlencode($newmail).'&key='.urlencode($key).'">Confirmez votre compte !</a><br/>
                        <p>Si vous n\'êtes pas à l\'origine de cette action, merci d\'ignorer ce mail</p>
                        </div>
                        </body>
                        </html>
                        ';
                        mail($newmail, "Changement de l'adresse mail de votre compte Jeu2dame.tk", $message, $header);
                        $erreur = "Un mail a été envoyé sur la nouvelle adresse mail. Confirmez ce changement en cliquant sur le lien inclut dans le mail.";
                    } else {
                        $erreur = "Une requête est déjà en attente, contactez un administrateur.";
                    }
                } else {
                    $erreur = "Cette adresse mail existe déjà.";
                }
            } else {
                $erreur = "Adresse mail trop longue.";
            }
        } else {
            $erreur = "Format de mail invalide.";
        }
    }
    if(isset($_POST['newmdp1']) AND !empty($_POST['newmdp1']) AND isset($_POST['newmdp2']) AND !empty($_POST['newmdp2'])) {
        $mdp1 = htmlspecialchars($_POST['newmdp1']);
        $mdp2 = htmlspecialchars($_POST['newmdp2']);
        if($mdp1 == $mdp2) {
            if(strlen($mdp1) >= 8) {
                $insertmdp = $db->prepare("UPDATE membres SET pswd = ? WHERE id = ?");
                $insertmdp->execute(array(password_hash($mdp1, PASSWORD_DEFAULT), $_SESSION['id']));
                $erreur = "Le mot de passe a bien été changé.";
            } else {
                $erreur = "Le mot de passe doit faire au minimum 8 caractères.";
            }
        } else {
            $erreur = "Vos deux mdp ne correspondent pas.";
        }
    }
    if(isset($_FILES['avatar']) AND !empty($_FILES['avatar']['name'])){
        $taillemax = 2097152;
        $extensionsvalides = array('jpg', 'jpeg', 'png', 'gif');
        if($_FILES['avatar']['size'] <= $taillemax){
            $extensionupload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.'), 1));
            if(in_array($extensionupload, $extensionsvalides)){
                $directory = "avatar/".$_SESSION['id'].".".$extensionupload;
                $move = move_uploaded_file($_FILES['avatar']['tmp_name'], $directory);
                if($move){
                    $req = $db->prepare('UPDATE membres SET avatar = ? WHERE id = ?');
                    $req->execute(array($_SESSION['id'].".".$extensionupload, $_SESSION['id']));
                    $erreur = "L'avatar a bien été modifié.";
                } else {
                    $erreur = "Erreur d'importation de l'avatar, réessayez.";
                }
            } else {
                $erreur = "Votre avatar doit être au format jpg, jpeg, png ou gif.";
            }
        } else {
            $erreur = "Votre avatar ne doit pas dépasser 2Mo.";
        }
    }
?>
<html lang="fr">
    <head>
        <title>Edition du profil de <?php echo $_SESSION['username']; ?> - Jeu2Dame</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/profil_edit.css">
    </head>
    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <button class="header_button" onclick="window.location.href = '../accounts/profil?id=<?= $_SESSION['id'] ?> ';">Retourner au profil</button>
        </header>
        <section>
            <div id="fenetre_centre">
                <h2>Edition de mon profil</h2>
                <br/><br/>
                <form method="POST" action="" enctype="multipart/form-data">
                    <label>Nom d'utilisateur</label><br/>
                    <input type="text" name="newusername" placeholder="username" value="<?php echo $user['username']; ?>" /><br />
                    <label>Mail</label><br/>
                    <input type="text" name="newmail" placeholder="Mail" value="<?php echo $user['mail']; ?>" /><br />
                    <label>Mot de passe</label><br/>
                    <input type="password" name="newmdp1" placeholder="Mot de passe"/><br />
                    <label>Confirmation - Mot de passe</label><br/>
                    <input type="password" name="newmdp2" placeholder="Confirmation du mot de passe" /><br /><br />
                    <label class="custom-file-upload" for="avatar">Photo de profil
                    <input type="file" placeholder="Ajouter un avatar" id="avatar" name="avatar"/></label><br/><br/>
                    <input type="submit" value="Mettre à jour mon profil !" />
                </form>
            <?php if(isset($erreur)) { echo $erreur; } ?>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
   </body>
</html>
<?php   
}
else {
   header("Location: ../login");
}
?>