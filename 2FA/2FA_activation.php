<?php
require('./2FA_config.php');

use RobThree\Auth\TwoFactorAuth;
$tfa = new TwoFactorAuth();

if (empty($_SESSION['tfa_secret'])){
    $_SESSION['tfa_secret'] = $tfa->createSecret();
}
$tfa_code = $_SESSION['tfa_secret'];

if (empty($_SESSION['id'])){
    header("Location: ../login");
    exit();
}

if (!empty($_POST['tfa_code_app'])){
    if ($tfa->verifyCode($tfa_code, $_POST['tfa_code_app'])){
        $req = $db->prepare('UPDATE membres SET tfa_code=? WHERE id=?');
        $req->execute(array($tfa_code, $_SESSION['id']));
        $erreur = "La double authentification a bien été activé.";
    } else {
        $erreur = "Code invalide";
    }
}

$req = $db->prepare('SELECT * FROM membres WHERE id=?');
$req->execute(array($_SESSION['id']));
$user = $req->fetch();

?>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Activation 2FA - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/2FA_activation.css">
    </head>
    
    <body>
        <header>
            <h1 class="header_titre">Jeu2Dame</h1>
            <button class="header_button" onclick="window.location.href = '../accounts/profil?id=<?= $_SESSION['id'] ?> ';">Annuler et retourner au profil</button>
        </header>
        
        <?php if(!$user['tfa_code']): ?>
            <section>
                <div id="fenetre_centre">
                <h1>Activation de la double authentification par application (2FA)</h1>
                <br/>
                <p>Scanner le QRCode ou taper le code secret manuellement dans votre application de double authentification.<br/>Nous vous recommandons d'utiliser Google Authenticator ou Microsoft Authenticator.</p>
                <br/>
                <img src="<?= $tfa->getQRCodeImageAsDataUri('Jeu2Dame.tk', $tfa_code) ?>"/>
                <p>Code secret : <?= $tfa_code ?></p>
                <br/>
                <form method="POST">
                    <input type="text" placeholder="Vérification Code" name="tfa_code_app">
                    <button type="submit">Valider</button>
                </form>
                <?php if(isset($erreur)){ echo "<p id='msg_erreur'>".$erreur."</p>"; } ?>
                </div>
            </section>
        <?php else: ?>
            <section>
                <div>
                <h1>Activation de la double authentification par application (2FA)</h1>
                <br/>
                <p>La double authentification est déjà activée sur votre compte.</p>
                <br/>
                <button onclick="window.location.href='2FA_desactivation'">Désactiver la double authentification</button>
                <br/>
                <?php if(isset($erreur)){ echo "<p id='msg_erreur'>".$erreur."</p>"; } ?>
                </div>
            </section>
        <?php endif ?>
        
        <footer>
            <div class="footer_titre">
                <h4>Développé par des gens sympathiques</h4>
            </div>
        </footer>
    </body>
</html>