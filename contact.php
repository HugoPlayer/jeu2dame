<?php
session_start();
?>
<html lang="fr">
    
    <head>
        <meta charset="utf-8">
        <title>Contact - Jeu2Dame</title>
        <link rel="stylesheet" href="style/border.css">
        <link rel="stylesheet" href="style/contact.css">
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
                <button onclick="window.location.href = '/login?redirect=contact';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_centre">
                <p class="fenetre_objet">Un problème ? Un bug ? Une demande particulière ou une suggestion ? Envoyez nous un mail pour répondre à votre demande. Si votre demande concerne un bug ou un problème, accompagnez votre mail d'une capture d'écran pour simplifier votre démarche.<br/></p>
                <br/>
                <button class="fenetre_objet" onclick="window.location.href = 'mailto:support@jeu2dame.tk';">Envoyer un mail</button>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button disabled onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>
