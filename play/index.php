<?php
session_start();
?>
<html lang="fr">
    
    <head>
        <meta charset="utf-8">
        <title>Jouer - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/play.css">
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
        <?php if(isset($_SESSION['id'])): ?>
        <section id="section_connected">
            <div id="section_titre">
                <h1>Lancer une partie</h1>
                <br/>
                <p>Choisissez le mode de jeu</p>
            </div>
            <div id="liste_gamemode">
                <div class="gamemode">
                    <h2>1Vs1 Solo</h2>
                    <p>Faites une partie contre notre robot Pedro !<br/>Attention, il pourrait vous battre...</p>
                    <img src="assets/robot.png" alt="Mode Bot"/>
                    <button disabled class="play_button" onclick="window.location.href = '/play/solo';">Jouer</button>
                </div>
                <div class="gamemode">
                    <h2>1Vs1 Multijoueur</h2>
                    <p>Jouer contre un traitre aléatoire sur le serveur.<br/>Mode de jeu recommandé quand vous n'avez pas d'ami.</p>
                    <img src="assets/epee.png" alt="Mode Multijoueur"/>
                    <button disabled class="play_button" onclick="window.location.href = '/play/multi';">Jouer</button>
                </div>
                <div class="gamemode">
                    <h2>1Vs1 Personnalisé</h2>
                    <p>Utilisez votre talent contre une personne de votre liste d'ami.<br/>Si vous perdez, c'est que VOUS n'avez pas de talent.</p>
                    <img src="assets/perso.png" alt="Mode Personnalisé"/>
                    <button class="play_button" onclick="window.location.href = '/play/perso';">Jouer</button>
                </div>
            </div>
        </section>
        <?php else: ?>
        <section id="section_disconnected">
            <div id="fenetre_centre">
                <h1>Lancer une partie</h1>
                <p>Pour lancer une partie, vous devez d'abord vous connecter.<br/>Créez dès maintenant un compte ou connectez vous en cliquant en haut à droite de votre écran.</p>
            </div>
        </section>
        <?php endif ?>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathique</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>