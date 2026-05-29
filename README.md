# DataPing

Plugin WordPress non-officiel pour afficher les données d'un club issues de l'[API Smartping](https://www.fftt.com) de la Fédération Française de Tennis de Table (FFTT).

> **Non affilié à la FFTT.** Plugin gratuit, maintenu bénévolement.

---

## Fonctionnalités

### Côté public

- **Liste des joueurs** — tableau trié par classement, avec badges de progression mensuelle et annuelle, filtrable par sexe (H / F / mixte)
- **Page d'équipe** — classement de poule mis en évidence + résultats de championnat organisés par journée
- **Feuilles de match** — au clic sur un résultat, la composition des deux équipes et le détail partie par partie s'affichent (chargement AJAX, mis en cache)

### Côté administration

- **Synchronisation manuelle** des données (joueurs + équipes) avec logs d'API en temps réel
- **Widget tableau de bord** avec bouton de synchronisation rapide
- **Gestion des équipes** — liste des équipes de championnat sénior, génération / suppression automatique des pages WordPress correspondantes (corbeille réversible)
- **Vue joueurs** — liste complète des licenciés du club

---

## Installation

1. Cloner ou télécharger ce dépôt dans `wp-content/plugins/DataPing/`
2. Activer le plugin dans *Extensions → Extensions installées*
3. Renseigner les identifiants API dans *DataPing → Paramètres* :
   - **ID Application** et **Mot de passe** fournis par la FFTT
   - **Numéro de club** (8 chiffres, ex. `10330011`)
4. Lancer une première synchronisation via le bouton *Synchroniser les données*

---

## Shortcodes

### Liste des joueurs

```
[joueurs type="MF"]
```

| Attribut | Valeurs | Défaut | Description |
|----------|---------|--------|-------------|
| `type` | `M`, `F`, `MF` | `MF` | Sexe affiché |

### Page d'équipe

```
[equipe iddiv="198511" idpoule="1140384"]
```

Les valeurs `iddiv` et `idpoule` sont générées automatiquement dans *DataPing → Équipes*.  
Copier le shortcode affiché dans le tableau et le coller dans la page WordPress souhaitée.

---

## Génération automatique de pages

Dans *DataPing → Équipes* :

1. Cocher les équipes à publier, décocher celles à supprimer
2. Cliquer sur **Appliquer la sélection**

Le plugin crée une page parent *Équipes* et une sous-page par équipe cochée, pré-remplie avec le shortcode correct. Les pages décochées sont envoyées à la corbeille (suppression réversible).

---

## Cache

Les données sont mises en cache via les **transients WordPress** :

| Données | Durée |
|---------|-------|
| Joueurs du club | Jusqu'à la prochaine sync |
| Classement de poule | 8h |
| Résultats par journée | 8h |
| Feuille de match | 7 jours (résultats passés) |

---

## Hooks pour développeurs

D'autres plugins peuvent consommer les données sans appel API supplémentaire :

```php
// Joueurs (retourne un tableau d'objets Joueur)
$joueurs = apply_filters('dataping_get_joueurs', 'MF'); // 'M', 'F' ou 'MF'

// Équipes (retourne un tableau d'objets Equipe)
$equipes = apply_filters('dataping_get_equipes', 'MF');

// Classement d'une poule
$classement = apply_filters('dataping_get_classement_poule', null, [
    'division' => '198511',
    'poule'    => '1140384',
]);

// Rencontres d'une poule
$rencontres = apply_filters('dataping_get_rencontres_poule', null, [
    'division' => '198511',
    'poule'    => '1140384',
]);
```

---

## Prérequis

- WordPress ≥ 5.0
- PHP ≥ 7.4
- Extension cURL activée
- Identifiants API FFTT valides (à demander auprès de la FFTT)

---

## Changelog

### En cours (dev)

- Feuilles de match expandables (composition + résultats partie par partie)
- Génération automatique de pages WordPress par équipe
- Design tableau unifié (classement, résultats, joueurs)
- Synchronisation avec logs d'API

### v0.3.0

- Optimisation des appels API (passage à `xml_licence_b.php`, suppression des endpoints inutiles)
- Ajout des champs catégorie, étranger, progressions mensuelle et annuelle
- Hooks WordPress pour exposer les données aux autres plugins
- Gestion du cache via transients

### v0.2.3

- Correction de bugs divers
- Affichage des résultats des joueurs

### v0.1

- Gestion des paramètres API
- Affichage des résultats des équipes d'un club

---

## Crédits

Merci à [Vincent Bab](mailto:vincentbab@gmail.com) pour la classe originale d'accès à l'API FFTT, adaptée pour ce plugin.

---

## Licence

[GPLv2](LICENSE)
