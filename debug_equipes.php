<?php
/**
 * Script de diagnostic pour identifier pourquoi les shortcodes sont vides
 *
 * Usage: Placer ce fichier à la racine du plugin DataPing et l'exécuter via WP
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Charger les classes nécessaires
require_once(__DIR__ . '/models/ParametresDataPing.php');
require_once(__DIR__ . '/models/AccesFFTTApi.php');

echo "<h1>Diagnostic des équipes DataPing</h1>\n";
echo "<pre>\n";

// 1. Vérifier les paramètres
echo "=== PARAMÈTRES ===\n";
echo "ID Application: " . ParametresDataPing::getIdApplication() . "\n";
echo "Num Club: " . ParametresDataPing::getNumClub() . "\n";
echo "\n";

// 2. Récupérer les équipes directement depuis l'API
echo "=== DONNÉES BRUTES DE L'API ===\n";
$api = AccesFFTTApi::getInstance();
$equipesM = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'M');
$equipesF = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'F');

echo "Nombre d'équipes Masculines: " . count($equipesM) . "\n";
echo "Nombre d'équipes Féminines: " . count($equipesF) . "\n";
echo "\n";

// 3. Examiner la première équipe en détail
if (!empty($equipesM)) {
    echo "=== ANALYSE PREMIÈRE ÉQUIPE MASCULINE ===\n";
    $premiere = $equipesM[0];
    echo "Structure complète:\n";
    print_r($premiere);
    echo "\n";

    echo "Champs critiques:\n";
    echo "  - libequipe: " . (isset($premiere['libequipe']) ? $premiere['libequipe'] : 'ABSENT') . "\n";
    echo "  - libdivision: " . (isset($premiere['libdivision']) ? $premiere['libdivision'] : 'ABSENT') . "\n";
    echo "  - liendivision: " . (isset($premiere['liendivision']) ? $premiere['liendivision'] : 'ABSENT') . "\n";
    echo "  - idpoule: " . (isset($premiere['idpoule']) ? $premiere['idpoule'] : 'ABSENT') . "\n";
    echo "  - iddiv: " . (isset($premiere['iddiv']) ? $premiere['iddiv'] : 'ABSENT') . "\n";
    echo "\n";

    // 4. Tester le parsing manuel du liendivision
    if (isset($premiere['liendivision']) && is_string($premiere['liendivision'])) {
        echo "=== TEST PARSING MANUEL ===\n";
        echo "Valeur brute de liendivision: " . $premiere['liendivision'] . "\n";

        $params = array();
        parse_str($premiere['liendivision'], $params);
        echo "Résultat de parse_str():\n";
        print_r($params);

        echo "Extraction:\n";
        echo "  - cx_poule: " . (isset($params['cx_poule']) ? $params['cx_poule'] : 'NON TROUVÉ') . "\n";
        echo "  - D1: " . (isset($params['D1']) ? $params['D1'] : 'NON TROUVÉ') . "\n";
        echo "\n";
    }
}

// 5. Tester la création d'un objet Equipe
if (!empty($equipesM)) {
    require_once(__DIR__ . '/models/Equipe.php');

    echo "=== TEST CRÉATION OBJET EQUIPE ===\n";
    try {
        $equipeTest = new Equipe($equipesM[0], 'M');
        echo "Objet créé avec succès!\n";
        echo "  - getLibequipe(): " . $equipeTest->getLibequipe() . "\n";
        echo "  - getLibdivision(): " . $equipeTest->getLibdivision() . "\n";
        echo "  - getIdpoule(): " . ($equipeTest->getIdpoule() ?: 'VIDE/NULL') . "\n";
        echo "  - getIddiv(): " . ($equipeTest->getIddiv() ?: 'VIDE/NULL') . "\n";
        echo "\n";

        // Générer le shortcode
        $shortcode = '[equipe iddiv="' . $equipeTest->getIddiv() . '" idpoule="' . $equipeTest->getIdpoule() . '"]';
        echo "Shortcode généré: " . $shortcode . "\n";
    } catch (Exception $e) {
        echo "ERREUR lors de la création: " . $e->getMessage() . "\n";
    }
}

// 6. Consulter les logs PHP récents
echo "\n=== LOGS D'API DATAPING (50 derniers) ===\n";
$logs = AccesFFTTApi::getApiLogs();
if (!empty($logs)) {
    foreach (array_slice($logs, -10) as $log) {
        echo "[{$log['time']}] [{$log['type']}] {$log['message']}\n";
    }
} else {
    echo "Aucun log disponible\n";
}

echo "</pre>\n";
