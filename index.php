<?php
session_start();
?>
<html lang="fr">
    
    <head>
        <meta charset="utf-8">
        <title>Accueil - Jeu2Dame</title>
        <link rel="stylesheet" href="style/border.css">
        <link rel="stylesheet" href="style/index.css">
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
                <button onclick="window.location.href = '/login?redirect=/';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_centre">
                <h1 class="fenetre_objet">Jeu2Dame, Le renouveau d'un classique</h1>
                <p class="fenetre_objet">Vous recherchez un jeu de société ? Un jeu multijoueur ? Les deux ?<br/>Inscrivez-vous et commencez dès maintenant à jouer avec vos amis</p>
                <div class="fenetre_objet">
                    <button class="fenetre_objet" onclick="window.location.href = '/play';">Jouer</button>
                    <button class="fenetre_objet" onclick="window.location.href = '/leaderboard';">Leaderboard</button>
                </div>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>
