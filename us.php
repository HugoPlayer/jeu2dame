<?php
session_start();
?>
<html lang="fr">
    
    <head>
        <meta charset="utf-8">
        <title>Qui sommes-nous ? - Jeu2Dame</title>
        <link rel="stylesheet" href="style/border.css">
        <link rel="stylesheet" href="style/us.css">
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
                <button onclick="window.location.href = '/login?redirect=us';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_centre">
                <h1>Qui sommes nous ?</h1>
                <br/>
                <p>
              Nous sommes 3 étudiants de l'université de Technologie de Belfort-Montbéliard. Ceci est notre projet dans le cadre de l'UV IF3A.
                </p>
            </div>
        </section>
        <footer>
            <button disabled onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>
