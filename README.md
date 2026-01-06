# DataPing

Plugin Wordpress non-officiel d'affichage des données issues de l'API de Fédération Française de Tennis de Table.

----------------------------------------------------------------------------------

### Changelog

**Version actuelle : v0.3.0**

* Optimisation des appels API - Suppression des endpoints inutiles (historique parties, tournois)
* Simplification des données stockées en cache
* Ajout des champs catégorie et étranger pour les joueurs
* Ajout de hooks WordPress pour exposer les données en cache aux autres plugins
* Conservation uniquement des données de base : sexe, étranger, catégorie, points (mensuels et officiels), progressions

**Version : v0.2.3**

* Correction de divers bugs
* Affichage des resultats des joueurs

**Version : v0.1**

* Gestion simple des paramètres de l'API
* Gestion simple de l'affichage des résultats des équipes d'un club

----------------------------------------------------------------------------------

### Hooks WordPress disponibles

Le plugin expose désormais les données en cache via des hooks WordPress, permettant à d'autres plugins d'accéder aux données sans faire de nouveaux appels API.

#### 1. Récupérer les joueurs

```php
// Récupérer tous les joueurs (hommes et femmes)
$joueurs = apply_filters('dataping_get_joueurs', 'MF');

// Récupérer uniquement les joueurs masculins
$joueurs_hommes = apply_filters('dataping_get_joueurs', 'M');

// Récupérer uniquement les joueuses féminines
$joueurs_femmes = apply_filters('dataping_get_joueurs', 'F');
```

Retourne un tableau d'objets `Joueur` avec les propriétés suivantes :
- `getNom()` : Nom du joueur
- `getPrenom()` : Prénom du joueur
- `getSexe()` : Sexe (M/F)
- `getCategorie()` : Catégorie d'âge
- `isEtranger()` : Booléen indiquant si le joueur est étranger
- `getClassement()->getPointsMensuels()` : Points mensuels
- `getClassement()->getPointsOfficiels()` : Points officiels
- `getClassement()->getProgressionAnnuelle()` : Progression annuelle

#### 2. Récupérer les équipes

```php
// Récupérer toutes les équipes
$equipes = apply_filters('dataping_get_equipes', null);

// Récupérer uniquement les équipes masculines
$equipes_hommes = apply_filters('dataping_get_equipes', 'M');

// Récupérer uniquement les équipes féminines
$equipes_femmes = apply_filters('dataping_get_equipes', 'F');
```

#### 3. Récupérer le classement d'une poule

```php
$classement = apply_filters('dataping_get_classement_poule', null, array(
    'division' => 'D1',
    'poule' => 'A'
));
```

#### 4. Récupérer les rencontres d'une poule

```php
$rencontres = apply_filters('dataping_get_rencontres_poule', null, array(
    'division' => 'D1',
    'poule' => 'A'
));
```

**Note** : Toutes les données retournées proviennent du cache WordPress (transients) avec une durée de vie de demi-journée (8h00, 13h00, lendemain 8h00).

----------------------------------------------------------------------------------

### Todolist

* Mise en base de données pour faire de la mise en cache des appels (actuellement très long pour récupérer l'ensemble des joueurs d'un club)
* Génération automatique des fiches joueurs (en page) avec sychro par wp-cron
* Top Progressions
* Paramétrage des colonnes à afficher dans la liste des joueurs
* et + si affinités :)

----------------------------------------------------------------------------------

### Commentaires

Le plugin se verra évoluer au fur et à mesure des semaines pour implémenter l'ensemble des fonctionnalités de l'API.
Merci à Vincent Bab (vincentbab@gmail.com), auteur de la classe de récupération des données (que j'ai adapté pour les besoin de la création de ce plugin).

Vous pouvez également télécharger le plugin depuis le repository wordpress : https://wordpress.org/plugins/wp-api-fftt/ (et le trouver dans la liste des plugins depuis l'administration de votre site wordpress).



