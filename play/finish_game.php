<?php
session_start();
require('../db.php');

if(isset($_SESSION['id'],$_SESSION['partie'])){
    $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
    $req->execute(array($_SESSION['partie']['id']));
    $partie = $req->fetch();
    
    if($_SESSION['id'] == $partie['id_gagnant']){
        $msg = "Vous avez gagné.";
    } else {
        $msg = "Vous avez perdu.";
    }
    
    unset($_SESSION['partie']);
} else {
    header("Location: /play");
}


?>
<html lang="fr">
    
    <head>
        <meta charset="utf-8">
        <title>Jouer - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/finish_game.css">
    </head>

    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <?php if(isset($_SESSION['id'])): ?>
            <nav class="navbar_connected">
                <?php if(isset($_SESSION['admin'])): ?>
                <button class="header_button" onclick="window.location.href = '/admin';">Panel d'administration</button>
                <?php endif ?>
                <button class="header_button" onclick="window.location.href = '/accounts/profil';">Profil</button>
                <button class="header_button" onclick="window.location.href = '/logout';">Déconnexion</button>
                <h4 id="username_hello"><?php echo('Bonjour ' . $_SESSION['username']); ?></h4>
            </nav>
            <?php else: ?>
            <nav>
                <button onclick="window.location.href = '/register';">Inscription</button>
                <button onclick="window.location.href = '/login?redirect=play';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_centre">
                <h2>Partie terminée.</h2>
                <?php 
                if(isset($msg)){
                    echo "<p>".$msg."</p>";
                }
                ?>
                <button onclick="window.location.href = '/'">Retour à l'accueil</button>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathique</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>