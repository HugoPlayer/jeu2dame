<?php
session_start();
require('./db.php');

if(isset($_SESSION['id'])){
    header("Location: /accounts/profil?id=".$_SESSION['id']);
}

if(isset($_POST['formconnexion'])) {
   $mailconnect = htmlspecialchars($_POST['mailconnect']);
   $mdpconnect = htmlspecialchars($_POST['mdpconnect']);
   if(!empty($mailconnect) AND !empty($mdpconnect)) {
      $req = $db->prepare("SELECT * FROM membres WHERE mail = ?");
      $req->execute(array($mailconnect));
      $userexist = $req->rowCount();
      if($userexist == 1) {
          $userinfo = $req->fetch();
          if(password_verify($mdpconnect, $userinfo['pswd'])){
              if($userinfo['mail_confirm']==1){
                if(!$userinfo['tfa_code']){
                    $_SESSION['id'] = $userinfo['id'];
                    $_SESSION['username'] = $userinfo['username'];
                    $_SESSION['mail'] = $userinfo['mail'];
                    $_SESSION['admin'] = $userinfo['admin'];
                    if(isset($_GET['redirect'])){
                        header("Location: ".$_GET['redirect']."");
                    } else {
                        header("Location: /");
                    }
                } else {
                    $_SESSION['mail'] = $userinfo['mail'];
                    $_SESSION['confirmkey'] = $userinfo['confirmkey'];
                    if(isset($_GET['redirect'])){
                        header("Location: /2FA/2FA_login?redirect=".$_GET['redirect']."");
                    } else {
                        header("Location: /2FA/2FA_login");
                    }
                }
              } else {
                  $erreur = "Votre adresse mail n'a pas été confirmé, consultez votre boite mail.";
              }
          } else {
              $erreur = "Mot de passe invalide.";
          }
      } else {
         $erreur = "Cette adresse mail n'existe pas.";
      }
   } else {
      $erreur = "Tous les champs doivent être complétés.";
   }
}

?>
<html lang="fr">
    <head>
        <title>Connexion - Jeu2Dame</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="style/border.css">
        <link rel="stylesheet" href="style/login.css">
        <link rel="stylesheet" href="style/stylebutton1.css">
    </head>
    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <nav>
                <button onclick="window.location.href = '/register';">Inscription</button>
                <button disabled onclick="window.location.href = '/login';">Connexion</button>
            </nav>
        </header>
        <section>
            <div id="fenetre_centre">
                <h2>Connexion</h2><br/>
                <form method="POST" action="">
                    <label for="mailconnect">Mail</label><br/>
                    <input type="email" name="mailconnect" placeholder="Mail" /><br/>
                    <label for="mdpconnect">Mot de passe</label><br/>
                    <input type="password" name="mdpconnect" placeholder="Mot de passe" /><br/><br/>
                    <input class="buttonsubmit" type="submit" name="formconnexion" value="Se connecter" /><br/>
                </form>
                <p class="phrase_erreur">
                <?php
                if(isset($erreur)) {
                    echo '<font color="red">'.$erreur."</font>";
                }
                ?>
                </p>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens ravagés</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
   </body>
</html>