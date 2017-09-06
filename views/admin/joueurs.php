<div class="wrap">
    <h1 class="DataPing_title">Les joueurs </h1>
    <h2>Shortcodes</h2>
    <p>Insérez le shortcode dans la page ou l'article où vous désirez afficher la liste des joueurs</p>
    <form class="DataPing_liste_admin">
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                    <th>Type</th>
                    <th>Shortcode</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row" class="check-column"></th>
                    <td>Ensemble des joueurs et joueuses</td>
                    <td>[joueurs]</td>
                </tr>
                <tr>
                    <th scope="row" class="check-column"></th>
                    <td>Ensemble des joueuses</td>
                    <td>[joueurs type='F']</td>
                </tr>
                <tr>
                    <th scope="row" class="check-column"></th>
                    <td>Ensemble des joueurs</td>
                    <td>[joueurs type='M']</td>
                </tr>
            </tbody>
        </table>
    </form>

    <h2>Liste des joueurs</h2>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th>Rang Nat</th>
            <th>Rang Dep</th>
            <th>Points mensuels</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Classement Off.</th>
            <th>Points Off.</th>
        </tr>
        </thead>
        <tbody id="the-list">
        <?php
        $joueurs = new Joueurs();
        foreach($joueurs->getJoueurs('MF') as $joueur):?>
            <?php
                /** @var Joueur $joueur */
            ?>
        <tr class="<?php echo $joueur->getSexe(); ?>">
            <td><?php echo $joueur->getClassement()->getRangNational() ; ?></td>
            <td><?php echo $joueur->getClassement()->getRangDepartemental() ; ?></td>
            <td><?php echo $joueur->getClassement()->getPointsMensuels() ; ?></td>
            <td class="bold"><?php echo $joueur->getNom() ; ?></td>
            <td class="bold"><?php echo $joueur->getPrenom() ; ?></td>
            <td><?php echo $joueur->getClassement()->getClassementOfficiel() ; ?></td>
            <td><?php echo $joueur->getClassement()->getPointsOfficiels() ; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
