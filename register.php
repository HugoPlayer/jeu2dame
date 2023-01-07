<?php
session_start();
require('./db.php');

if(isset($_SESSION['id'])){
    header("Location: /accounts/profil?id=".$_SESSION['id']);
}

if(isset($_POST['forminscription'])) {
    $username = htmlspecialchars($_POST['username']);
    $mail = htmlspecialchars($_POST['mail']);
    $mail2 = htmlspecialchars($_POST['mail2']);
    $mdp = htmlspecialchars($_POST['mdp']);
    $mdp2 = htmlspecialchars($_POST['mdp2']);
    if(!empty($_POST['username']) AND !empty($_POST['mail']) AND !empty($_POST['mail2']) AND !empty($_POST['mdp']) AND !empty($_POST['mdp2'])) {
        $usernamelength = strlen($username);
        if($usernamelength <= 20) {
            $requsername = $db->prepare("SELECT * FROM membres WHERE username = ?");
            $requsername->execute(array($username));
            $usernameexist = $requsername->rowCount();
            if($usernameexist == 0) {
                if($mail == $mail2 AND strlen($mail)<150) {
                    if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                        $reqmail = $db->prepare("SELECT * FROM membres WHERE mail = ?");
                        $reqmail->execute(array($mail));
                        $mailexist = $reqmail->rowCount();
                        if($mailexist == 0) {
                            if($mdp == $mdp2 AND strlen($mdp)>=8) {
                                $longueurKey = 40;
                                $key = "";
                                for($i=1;$i<$longueurKey;$i++) {
                                    $key .= mt_rand(0,9);
                                }
                                $insertmbr = $db->prepare("INSERT INTO membres(username, mail, pswd, confirmkey, mail_confirm) VALUES(?, ?, ?, ?, ?)");
                                $insertmbr->execute(array($username, $mail, password_hash($mdp, PASSWORD_DEFAULT), $key, 0));
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
                                <h1>Vous avez demandé a créer un compte Jeu2Dame.tk.<br/>Pour confirmer, cliquez sur le lien ci-dessous.</h1>
                                <a href="http://jeu2dame.tk/mail_confirmation?origin=create&mail='.urlencode($mail).'&key='.urlencode($key).'">Confirmez votre compte !</a><br/>
                                <p>Si vous n\'êtes pas à l\'origine de cette action, merci d\'ignorer ce mail.</p>
                                </div>
                                </body>
                                </html>
                                ';
                                mail($mail, "Confirmation de votre compte Jeu2dame.tk", $message, $header);
                                $erreur = 'Votre compte a bien été créé ! <a href="/login">Se connecter</a>';
                            } else {
                                $erreur = "Vos mots de passes ne correspondent pas ou il ne fait pas au minimum 8 caractères.";
                            }
                        } else {
                            $erreur = "Adresse mail déjà utilisée.";
                        }
                    } else {
                        $erreur = "Votre adresse mail n'est pas valide.";
                    }
                } else {
                    $erreur = "Vos adresses mail ne correspondent pas.";
                }
            } else {
                $erreur = "Ce nom d'utilisateur existe déjà.";
            }   
        } else {
            $erreur = "Votre nom d'utilisateur ne doit pas dépasser 20 caractères !";
        }
    } else {
        $erreur = "Tous les champs doivent être complétés !";
    }
}
?>
<html lang="fr">
    <head>
        <title>Inscription - Jeu2Dame</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="style/border.css">
        <link rel="stylesheet" href="style/register.css">
        <link rel="stylesheet" href="style/stylebutton1.css">
    </head>
    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <nav>
                <button disabled class="header_button" onclick="window.location.href = '/register';">Inscription</button>
                <button class="header_button" onclick="window.location.href = '/login';">Connexion</button>
            </nav>
        </header>
        <section>
            <div id="fenetre_centre">
                <h2 class="inscription" >Inscription</h2>
                <br /><br />
                <form method="POST" action="">
                    <label for="username">Nom d'utilisateur*</label><br/>
                    <input type="text" placeholder="Votre username" id="username" name="username" value="<?php if(isset($username)) { echo $username; } ?>" /><br/>
                    <label for="mail">Mail*</label><br/>
                    <input type="email" placeholder="Votre mail" id="mail" name="mail" value="<?php if(isset($mail)) { echo $mail; } ?>" /><br/>
                    <label for="mail2">Confirmation du mail*</label><br/>
                    <input type="email" placeholder="Confirmez votre mail" id="mail2" name="mail2" value="<?php if(isset($mail2)) { echo $mail2; } ?>" /><br/>
                    <label for="mdp">Mot de passe*</label><br/>
                    <input type="password" placeholder="Votre mot de passe" id="mdp" name="mdp" /><br/>
                    <label for="mdp2">Confirmation du mot de passe*</label><br/>
                    <input type="password" placeholder="Confirmez votre mdp" id="mdp2" name="mdp2" /><br/><br/>
                <input class="buttonsubmit" type="submit" name="forminscription" value="Je m'inscris" align="center"/>
                </form>
                <br/>
                <p class="phrase_erreur">
                <?php
                if(isset($erreur))
                {
                    echo '<font color="red">'.$erreur."</font>";
                }
                ?>
                </p>
                <br/>
                <p><em>Les champs comportant une * sont obligatoires.</em></p>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
   </body>
</html>