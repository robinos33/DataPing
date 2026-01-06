# Guide de diagnostic - Problème de synchronisation DataPing

## Symptôme
Les listings de joueurs et équipes sont vides après synchronisation.

## Architecture du système

DataPing utilise **WordPress Transients** (cache temporaire) au lieu d'une base de données persistante :
- Les données sont stockées dans `wp_options` via `set_transient()`
- Le cache expire toutes les 8 heures (8h00, 13h00, lendemain 8h00)
- La synchronisation est **manuelle** via un bouton dans l'admin WordPress
- Aucune synchronisation automatique (pas de cronjob ni GitHub Actions)

## Étapes de diagnostic

### 1. Vérifier la configuration API FFTT

Dans WordPress Admin > DataPing :
- **ID Application** : doit être fourni par la FFTT (format: `AM001`)
- **Mot de passe** : clé API fournie par la FFTT
- **Numéro de club** : numéro du club à synchroniser

⚠️ **Si l'un de ces paramètres est vide, la synchronisation échouera immédiatement**

### 2. Lancer la synchronisation manuelle

1. Aller dans WordPress Admin > DataPing
2. Cliquer sur "Synchroniser les données"
3. Observer le message de retour :
   - ✅ **Succès** : affiche le nombre de joueurs et équipes récupérés
   - ❌ **Erreur** : affiche le message d'erreur détaillé

### 3. Vérifier la console du navigateur

Ouvrir la console développeur (F12) et observer :
- `DataPing Sync - Résultats:` → montre les nombres de joueurs/équipes
- `DataPing Sync - Debug:` → logs détaillés du processus

**Exemples de logs attendus :**
```
DataPing Sync - Debug: [
  "Numéro de club: 08350194",
  "ID Application: AM001",
  "Cache joueurs effacé",
  "Joueurs récupérés: 25",
  "Cache équipes effacé",
  "Équipes M récupérées: 3",
  "Équipes F récupérées: 1"
]
```

### 4. Vérifier les logs d'erreur PHP

Les erreurs d'API sont maintenant loggées dans les logs PHP de WordPress :

```bash
# Localiser le fichier de logs WordPress
# Généralement dans wp-content/debug.log
tail -f wp-content/debug.log | grep "DataPing"
```

**Types d'erreurs possibles :**
- `DataPing - Erreur cURL` : problème de connexion réseau
- `DataPing - Code HTTP XXX` : l'API FFTT a retourné une erreur
- `DataPing - Erreur parsing XML` : la réponse de l'API est invalide

### 5. Cas d'erreur courants

| Erreur | Cause | Solution |
|--------|-------|----------|
| `Numéro de club non configuré` | Paramètre manquant | Remplir le numéro de club dans l'admin |
| `Identifiants API FFTT non configurés` | ID/Mot de passe vide | Demander les identifiants à la FFTT |
| `0 joueurs récupérés` | Club inexistant ou API non configurée | Vérifier le numéro de club |
| `Erreur cURL (6): Could not resolve host` | Problème réseau | Vérifier la connexion internet du serveur |
| `Code HTTP 401` | Identifiants invalides | Vérifier l'ID et le mot de passe API |
| `Code HTTP 500` | Erreur serveur FFTT | Réessayer plus tard |

## Flux de synchronisation

```
1. Utilisateur clique sur "Synchroniser"
   ↓
2. Vérification des paramètres (club, ID app, mot de passe)
   ↓
3. Effacement du cache existant (delete_transient)
   ↓
4. Appel API FFTT pour récupérer :
   - Liste des licenciés du club
   - Pour chaque licencié : données licence + classement
   - Équipes masculines et féminines
   - Pour chaque équipe : classement et rencontres
   ↓
5. Stockage dans les transients WordPress (set_transient)
   ↓
6. Enregistrement du timestamp de sync
   ↓
7. Retour JSON avec résultats + logs debug
```

## Fichiers modifiés pour le debug

### `DataPing.php` (lignes 285-343)
- Ajout de vérification des paramètres avant synchronisation
- Logs détaillés à chaque étape
- Retour des informations de debug dans la réponse AJAX

### `models/AccesFFTTApi.php` (lignes 364-398)
- Capture des erreurs cURL (errno, message, HTTP code)
- Logs d'erreurs dans error_log
- Validation du parsing XML avec détails de l'erreur

### `views/admin/admin.php` (lignes 60-84)
- Affichage du nombre de joueurs/équipes dans le message de succès
- Logs console.log pour le debug navigateur
- Logs console.error en cas d'erreur

## Test rapide

Pour tester si l'API FFTT fonctionne, essayer manuellement :

```bash
# Remplacer XXX par vos paramètres
curl "http://www.fftt.com/mobile/pxml/xml_liste_joueur.php?club=08350194&id=AM001&serie=YOUR_SERIAL&tm=20260106123456789&tmc=YOUR_HASH"
```

Si cela retourne du XML, l'API fonctionne. Sinon, le problème vient des identifiants ou du réseau.
