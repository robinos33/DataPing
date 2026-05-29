<?php require_once(__DIR__ . '/header.php'); ?>
<div class="DataPing_div">
    <?php
    foreach ($listeEquipes as $equipe) {
        if ($atts['iddiv'] === $equipe['iddiv'] && $atts['idpoule'] === $equipe['idpoule']) {
            $classementPoule = $api->getPouleClassement($equipe['iddiv'], $equipe['idpoule']);
            ?>
            <h4 class="dataping-equipe-titre">
                <?php echo esc_html($equipe['libdivision']); ?>
                <span class="dataping-equipe-sous-titre">— <?php echo esc_html($equipe['libequipe']); ?></span>
            </h4>

            <h5 class="dataping-section-titre">Classement</h5>
            <table class="dataping-table">
                <thead>
                <tr>
                    <th class="classement">Class.</th>
                    <th class="equipe">Équipe</th>
                    <th class="joues">Joués</th>
                    <th class="points">Pts</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = 0;
                foreach ($classementPoule as $classement) {
                    $classEquipe = '';
                    if (preg_match('/' . preg_quote($classement['equipe'], '/') . '/', $equipe['libequipe'])) {
                        $classEquipe = 'equipe_club';
                    }
                    $i++;
                    $class = ($i % 2 == 0) ? 'odd' : 'even';
                    ?>
                    <tr class="<?php echo esc_attr(trim($class . ' ' . $classEquipe)); ?>">
                        <td class="center"><?php echo esc_html($classement['clt']); ?></td>
                        <td><?php echo esc_html($classement['equipe']); ?></td>
                        <td class="center"><?php echo esc_html($classement['joue']); ?></td>
                        <td class="center"><?php echo esc_html($classement['pts']); ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>

            <?php $rencontresPoule = $api->getPouleRencontres($equipe['iddiv'], $equipe['idpoule']); ?>
            <h5 class="dataping-section-titre">Résultats par journée</h5>
            <?php
            $numJournee = 0;
            $journee    = '';
            foreach ($rencontresPoule as $rencontre) {
                if ($journee !== $rencontre['libelle']) {
                    if ($numJournee !== 0) {
                        echo '</tbody></table>';
                    }
                    $journee = $rencontre['libelle'];
                    $numJournee++;
                    echo '<table class="dataping-table dataping-rencontres" id="journee' . esc_attr($numJournee) . '">';
                    echo '<caption>' . esc_html($journee) . '</caption>';
                    echo '<tbody>';
                }
                $scoreA = !is_array($rencontre['scorea']) ? esc_html($rencontre['scorea']) : '';
                $scoreB = !is_array($rencontre['scoreb']) ? esc_html($rencontre['scoreb']) : '';
                ?>
                <tr>
                    <td class="equipes left"><?php echo esc_html($rencontre['equa']); ?></td>
                    <td class="score center dataping-score"><?php echo $scoreA; ?></td>
                    <td class="tiret center dataping-tiret"> - </td>
                    <td class="score center dataping-score"><?php echo $scoreB; ?></td>
                    <td class="equipes right"><?php echo esc_html($rencontre['equb']); ?></td>
                </tr>
                <?php
            }
            if ($numJournee > 0) {
                echo '</tbody></table>';
            }

            // Dernière mise à jour
            if (method_exists($api, 'getCacheUpdatedAt')) {
                $u1      = $api->getCacheUpdatedAt('poule_classement', array('D1' => $equipe['iddiv'], 'cx_poule' => $equipe['idpoule']));
                $u2      = $api->getCacheUpdatedAt('poule_rencontres', array('D1' => $equipe['iddiv'], 'cx_poule' => $equipe['idpoule']));
                $updated = 0;
                if ($u1 !== false) { $updated = (int) max($updated, $u1); }
                if ($u2 !== false) { $updated = (int) max($updated, $u2); }
                if ($updated > 0) {
                    $formatted = function_exists('date_i18n')
                        ? date_i18n('d/m/Y H:i', $updated, false)
                        : date('d/m/Y H:i', $updated);
                    echo '<p class="dataping-updated-at">Dernière mise à jour : ' . esc_html($formatted) . '</p>';
                }
            }
        }
    }
    ?>
</div>
