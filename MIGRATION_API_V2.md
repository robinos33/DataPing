# Migration vers l'API FFTT v2

## Résumé des modifications

Ce document décrit les changements apportés pour résoudre le problème des shortcodes vides dans la liste des équipes.

### Problème identifié

Les shortcodes affichés dans la liste des équipes étaient vides :
```
[equipe iddiv="" idpoule=""]
```

### Causes

1. **URL d'API obsolète** : Le code utilisait l'ancienne URL `http://www.fftt.com/mobile/pxml/` qui a été remplacée par `https://apiv2.fftt.com/mobile/pxml/`

2. **Parsing fragile du champ `liendivision`** : Le code ne gérait pas tous les formats possibles de ce champ

3. **Logging insuffisant** : Difficile de diagnostiquer le problème sans logs détaillés

## Modifications apportées

### 1. Migration vers l'API v2 (AccesFFTTApi.php)

Toutes les URLs d'API ont été mises à jour :

**Avant :**
```php
http://www.fftt.com/mobile/pxml/xml_equipe.php
http://www.fftt.com/mobile/pxml/xml_result_equ.php
// etc.
```

**Après :**
```php
https://apiv2.fftt.com/mobile/pxml/xml_equipe.php
https://apiv2.fftt.com/mobile/pxml/xml_result_equ.php
// etc.
```

**Fichiers modifiés :**
- `models/AccesFFTTApi.php` (lignes 107, 113, 119, 124, 144, 149, 161, 192, 210, 219, 226, 231, 240)

### 2. Amélioration du parsing de `liendivision`

Le parsing du champ `liendivision` est maintenant plus robuste et gère :

- Les query strings simples : `D1=12345&cx_poule=67890`
- Les URLs complètes : `http://example.com/page?D1=12345&cx_poule=67890`
- Les valeurs vides ou absentes

**Code ajouté (AccesFFTTApi.php:171-178) :**
```php
if (isset($team['liendivision']) && is_string($team['liendivision']) && !empty($team['liendivision'])) {
    $liendivision = $team['liendivision'];

    // Si c'est une URL complète, extraire seulement la query string
    if (strpos($liendivision, '?') !== false) {
        $urlParts = parse_url($liendivision);
        $liendivision = isset($urlParts['query']) ? $urlParts['query'] : $liendivision;
    }

    parse_str($liendivision, $params);
    $team['idpoule'] = isset($params['cx_poule']) ? $params['cx_poule'] : null;
    $team['iddiv'] = isset($params['D1']) ? $params['D1'] : null;
```

### 3. Logging détaillé

Ajout de logs détaillés pour faciliter le diagnostic :

**Types de logs :**
- `SUCCESS` : Parsing réussi avec iddiv et idpoule extraits
- `WARNING` : Parsing effectué mais paramètres manquants
- `ERROR` : Champ liendivision absent, vide ou invalide

**Exemple de logs :**
```
DataPing - SUCCESS - Team: US TALENCE 1 - liendivision: D1=12345&cx_poule=67890 - iddiv: 12345, idpoule: 67890
DataPing - WARNING - Team: US TALENCE 2 - liendivision parsé mais paramètres manquants - Raw: action=classement - Params trouvés: {"action":"classement"}
DataPing - ERROR - Team: US TALENCE 3 - liendivision absent, vide ou non-string - Type: absent - Value: N/A
```

**Fichiers modifiés :**
- `models/AccesFFTTApi.php` (lignes 166-200)

## Instructions de mise en œuvre

### 1. Déployer les modifications

Les fichiers suivants ont été modifiés :
- `models/AccesFFTTApi.php`

### 2. Vider le cache et resynchroniser

**IMPORTANT** : Après avoir déployé les modifications, vous DEVEZ resynchroniser les données pour que les changements prennent effet.

#### Via l'interface WordPress Admin :

1. Connectez-vous à l'admin WordPress
2. Allez dans **DataPing > Paramètres**
3. Cliquez sur le bouton **"Synchroniser les données"**
4. Attendez que la synchronisation se termine

#### Via le script de diagnostic :

Vous pouvez également utiliser le script de diagnostic pour voir les détails :
```
http://votre-site.com/wp-content/plugins/DataPing/debug_equipes.php
```

### 3. Vérifier les résultats

1. Allez dans **DataPing > Équipes**
2. Vérifiez que les shortcodes affichent maintenant les bons paramètres :
   ```
   [equipe iddiv="12345" idpoule="67890"]
   ```

### 4. Consulter les logs

Si le problème persiste :

1. Allez dans **DataPing > Paramètres**
2. Consultez la section **"Logs de l'API FFTT"**
3. Recherchez les lignes commençant par `DataPing -`
4. Les logs indiquent :
   - Si le parsing a réussi (`SUCCESS`)
   - Si des paramètres sont manquants (`WARNING`)
   - Si le champ liendivision est invalide (`ERROR`)

## Compatibilité

- **WordPress** : Testé avec WordPress 5.x et 6.x
- **PHP** : Nécessite PHP 7.0 ou supérieur
- **API FFTT** : Compatible avec API Smartping 2.0 (https://apiv2.fftt.com)

## Références

- [Documentation officielle API FFTT Smartping 2.0](http://www.fftt.com/site/medias/shares_files/informatique-specifications-techniques-api-smartping-720.pdf)
- [Page officielle API FFTT](https://www.fftt.com/site/mediatheque/autres-medias/api)
- [Référence GitHub vincentbab/ffttapi](https://github.com/vincentbab/ffttapi/blob/master/Service.php)

## Support

En cas de problème :

1. Vérifiez les logs de l'API FFTT dans l'admin WordPress
2. Vérifiez que vos identifiants API FFTT sont valides
3. Vérifiez que le numéro de club est correct (format 8 chiffres)
4. Consultez les logs PHP de votre serveur pour voir les messages `DataPing -`

## Auteur

Modifications effectuées le 2026-01-13 pour résoudre le problème des shortcodes vides.
