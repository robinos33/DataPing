<?php
/**
 * Script de test pour vérifier l'API FFTT
 * À lancer depuis WordPress ou en standalone avec les bonnes constantes
 */

// Si on lance depuis WordPress
if (file_exists('../../wp-load.php')) {
    require_once('../../wp-load.php');
    require_once(__DIR__ . '/Utils.php');
    require_once(__DIR__ . '/models/ParametresDataPing.php');
    require_once(__DIR__ . '/models/AccesFFTTApi.php');

    echo "<h1>Test API FFTT - DataPing</h1>";
    echo "<pre>";

    // Récupérer les paramètres
    $numClub = ParametresDataPing::getNumClub();
    $idApp = ParametresDataPing::getIdApplication();
    $motDePasse = ParametresDataPing::getMotDePasse();

    echo "=== PARAMÈTRES ===\n";
    echo "Numéro de club: " . ($numClub ?: 'NON CONFIGURÉ') . "\n";
    echo "ID Application: " . ($idApp ?: 'NON CONFIGURÉ') . "\n";
    echo "Mot de passe: " . ($motDePasse ? str_repeat('*', strlen($motDePasse)) : 'NON CONFIGURÉ') . "\n\n";

    if (empty($numClub) || empty($idApp) || empty($motDePasse)) {
        echo "❌ ERREUR: Paramètres manquants. Configurez-les dans WordPress Admin > DataPing\n";
        exit;
    }

    // Initialiser l'API
    $api = AccesFFTTApi::getInstance();

    echo "=== TEST 1: Récupération des licenciés du club ===\n";
    $licencies = $api->getLicencesByClub($numClub);
    echo "Nombre de licenciés: " . count($licencies) . "\n";
    if (count($licencies) > 0) {
        echo "Premier licencié: " . json_encode($licencies[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "⚠️  Aucun licencié trouvé\n";
    }
    echo "\n";

    echo "=== TEST 2: Récupération des équipes masculines ===\n";
    $equipesM = $api->getEquipesByClub($numClub, 'M');
    echo "Nombre d'équipes M: " . count($equipesM) . "\n";
    if (count($equipesM) > 0) {
        echo "Première équipe M: " . json_encode($equipesM[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "⚠️  Aucune équipe masculine trouvée\n";
    }
    echo "\n";

    echo "=== TEST 3: Récupération des équipes féminines ===\n";
    $equipesF = $api->getEquipesByClub($numClub, 'F');
    echo "Nombre d'équipes F: " . count($equipesF) . "\n";
    if (count($equipesF) > 0) {
        echo "Première équipe F: " . json_encode($equipesF[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "⚠️  Aucune équipe féminine trouvée\n";
    }
    echo "\n";

    echo "=== LOGS PHP (error_log) ===\n";
    echo "Consultez les logs PHP pour voir les détails des appels API:\n";
    echo "- Les URLs appelées\n";
    echo "- Les codes HTTP retournés\n";
    echo "- Les erreurs cURL éventuelles\n";
    echo "- Le contenu XML retourné\n\n";

    echo "Pour voir les logs en temps réel:\n";
    echo "tail -f " . ini_get('error_log') . " | grep DataPing\n";

    echo "</pre>";

} else {
    echo "Ce script doit être lancé depuis WordPress ou avec wp-load.php accessible.\n";
}
