<?php require_once(__DIR__ . '/front-header.php'); ?>
<div class="fftt_plug_div">
    <?php
    foreach ($listeEquipes as $equipe) {
        if ($atts['iddiv'] === $equipe['iddiv'] && $atts['idpoule'] === $equipe['idpoule']) {
            $classementPoule = $api->getPouleClassement($equipe['iddiv'], $equipe['idpoule']);
            ?>
            <h4><?php echo $equipe['libdivision'] . ' - ' . $equipe['libequipe']; ?></h4>
            <h5>Classement</h5>
            <table>
                <thead>
                <th class="classement">Class.</th>
                <th class="equipe">Equipe</th>
                <th class="joues">Joués</th>
                <th class="points">Points</th>
                </thead>
                <?php
                $i = 0;
                foreach ($classementPoule as $classement) {
                    //Affichage différent pour l'équipe du club
                    $classEquipe = '';
                    if (preg_match('/' . $classement['equipe'] . '/', $equipe['libequipe'])) {
                        $classEquipe = 'equipe_club';
                    }

                    //Affichage pair/impair
                    $i++;
                    if ($i % 2 == 0) {
                        $class = 'odd';
                    } else {
                        $class = 'even';
                    }
                    ?>

                    <tr class="<?php echo $classEquipe . ' ' . $class; ?>">
                        <td class="center"><?php echo $classement['clt']; ?></td>
                        <td><?php echo $classement['equipe']; ?></td>
                        <td class="center"><?php echo $classement['joue']; ?></td>
                        <td class="center"><?php echo $classement['pts']; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <?php $rencontresPoule = $api->getPouleRencontres($equipe['iddiv'], $equipe['idpoule']); ?>
            <h5>Résultats par journée</h5>
            <?php
            $numJournee = 0;
            $journee = '';
            foreach ($rencontresPoule as $rencontre) {
                if ($journee !== $rencontre['libelle']) {
                    if ($numJournee !== 0) {
                        echo '</table>';
                    }
                    $journee = $rencontre['libelle'];
                    $numJournee++;

                    echo '<table class="fftt_plug_rencontres_table" id=\'journee' . $numJournee . '\'>';
                    echo '<caption colspan="5" class="center">' . $journee . '</caption>';
                }
                ?>
                <tr>
                    <td class="equipes left"><?php echo $rencontre['equa']; ?></td>
                    <td class="score center"><?php
                        if (!is_array($rencontre['scorea'])) {
                            echo $rencontre['scorea'];
                        }
                        ?></td>
                    <td class="tiret center"> - </td>
                    <td class="score center"><?php
                        if (!is_array($rencontre['scoreb'])) {
                            echo $rencontre['scoreb'];
                        }
                        ?></td>
                    <td class="equipes right"><?php echo $rencontre['equb']; ?></td>
                </tr>

                <?php
                //echo 'numjournée : ' . $numJournee . ' rencontresPoules : ' . count($rencontre) . '<br />';
            }
            echo '</table>';
        }
        ?>
        <?php
    }
    ?>
</div>