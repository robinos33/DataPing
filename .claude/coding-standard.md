# Standard de Développement DataPing

## Philosophie de développement

Ce projet suit une approche **Test-Driven Development (TDD)** avec une architecture **Domain-Driven Design (DDD)** en PHP 8.2.

## Règles impératives

### 1. Tests Unitaires OBLIGATOIRES

**AVANT tout développement :**
- Écrire les tests unitaires pour toute nouvelle fonctionnalité
- Écrire les tests avant le code (TDD)
- Couverture de code minimale : 80%
- Chaque classe du domaine doit avoir sa classe de test

**Structure des tests :**
```
tests/
├── Unit/
│   ├── Domain/
│   │   ├── Model/
│   │   ├── ValueObject/
│   │   ├── Service/
│   │   └── Repository/
│   ├── Application/
│   │   └── UseCase/
│   └── Infrastructure/
└── Integration/
```

### 2. Architecture DDD

**Structure obligatoire :**
```
src/
├── Domain/                    # Cœur métier (aucune dépendance externe)
│   ├── Model/                # Entités et Agrégats
│   ├── ValueObject/          # Objets valeur immuables
│   ├── Repository/           # Interfaces de repositories
│   ├── Service/              # Services du domaine
│   └── Exception/            # Exceptions métier
├── Application/              # Cas d'usage
│   ├── UseCase/              # Actions métier
│   ├── DTO/                  # Data Transfer Objects
│   └── Service/              # Services applicatifs
└── Infrastructure/           # Implémentation technique
    ├── Repository/           # Implémentation des repositories
    ├── API/                  # Clients API externes
    ├── Cache/                # Système de cache
    └── Persistence/          # Accès base de données
```

### 3. Processus de développement

**Workflow obligatoire :**

1. **RED** - Écrire le test qui échoue
   ```php
   public function test_should_create_joueur_with_valid_data(): void
   {
       // Arrange
       $licence = new Licence('1234567');
       $nom = new Nom('Dupont');

       // Act
       $joueur = new Joueur($licence, $nom);

       // Assert
       $this->assertEquals('1234567', $joueur->getLicence()->getValue());
   }
   ```

2. **GREEN** - Écrire le code minimal pour faire passer le test
   ```php
   final class Joueur
   {
       public function __construct(
           private readonly Licence $licence,
           private readonly Nom $nom
       ) {}

       public function getLicence(): Licence
       {
           return $this->licence;
       }
   }
   ```

3. **REFACTOR** - Améliorer le code tout en gardant les tests verts
   - Éliminer la duplication
   - Améliorer la lisibilité
   - Respecter les principes SOLID
   - Documenter avec PHPDoc complet

4. **DOCUMENTATION** - Documenter systématiquement
   ```php
   /**
    * Représente un joueur de tennis de table
    *
    * Agrégat racine du contexte Joueur
    * Un joueur est identifié de manière unique par sa licence FFTT
    *
    * @package DataPing\Domain\Model
    */
   final class Joueur
   {
       /**
        * Crée une nouvelle instance de joueur
        *
        * @param Licence $licence Numéro de licence FFTT unique
        * @param Nom $nom Nom du joueur
        *
        * @throws InvalidLicenceException Si la licence est invalide
        */
       public function __construct(
           private readonly Licence $licence,
           private readonly Nom $nom
       ) {}
   }
   ```

### 4. Standards de code PHP 8.2

**Obligatoire :**
- Types stricts : `declare(strict_types=1);`
- Propriétés typées avec readonly quand applicable
- Return types sur toutes les méthodes
- Constructeur property promotion
- Named arguments pour clarté
- Enums pour les constantes métier
- Attributes pour metadata

**Exemple :**
```php
<?php

declare(strict_types=1);

namespace DataPing\Domain\ValueObject;

/**
 * Représente un classement FFTT
 */
final readonly class Classement
{
    public function __construct(
        private int $points,
        private ClassementType $type = ClassementType::OFFICIEL
    ) {
        if ($points < 0 || $points > 3500) {
            throw new InvalidClassementException(
                "Le classement doit être entre 0 et 3500"
            );
        }
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function isProgressionPossible(): bool
    {
        return $this->points < 3500;
    }
}

enum ClassementType: string
{
    case OFFICIEL = 'officiel';
    case MENSUEL = 'mensuel';
}
```

### 5. Tests : Règles spécifiques

**Nomenclature :**
- `test_should_[comportement_attendu]_when_[condition]`
- Ou : `test_[méthode]_[scenario]_[résultat_attendu]`

**Structure AAA (Arrange-Act-Assert) :**
```php
public function test_should_throw_exception_when_licence_invalid(): void
{
    // Arrange
    $licenceInvalide = '123'; // trop court

    // Act & Assert
    $this->expectException(InvalidLicenceException::class);
    new Licence($licenceInvalide);
}
```

**Tests des Value Objects :**
- Test d'égalité
- Test d'immutabilité
- Test des validations
- Test des cas limites

**Tests des Entités :**
- Test de création
- Test des méthodes métier
- Test des invariants
- Test des événements du domaine

### 6. Documentation obligatoire

**Pour chaque classe :**
- Description du rôle
- Pattern DDD utilisé (Entity, Value Object, Service, etc.)
- Relations avec autres classes
- Exemples d'utilisation si pertinent

**Pour chaque méthode publique :**
- Description de l'action
- @param avec type et description
- @return avec type et description
- @throws pour toutes les exceptions possibles

**README dans chaque namespace :**
Créer un README.md expliquant le contexte bounded du DDD

## Commandes de vérification

```bash
# Lancer les tests
composer test

# Vérifier la couverture
composer test:coverage

# Analyse statique
composer analyse

# Standards de code
composer cs:check
composer cs:fix
```

## Checklist avant commit

- [ ] Tests unitaires écrits et passent (100%)
- [ ] Couverture de code > 80%
- [ ] Code refactoré (pas de duplication)
- [ ] Documentation PHPDoc complète
- [ ] Types stricts activés
- [ ] Analyse statique sans erreur
- [ ] Standards de code respectés
- [ ] README mis à jour si nécessaire

## Principes DDD à respecter

1. **Ubiquitous Language** : Le code utilise le vocabulaire métier exact
2. **Bounded Context** : Chaque contexte est isolé et cohérent
3. **Aggregates** : Chaque agrégat protège ses invariants
4. **Value Objects** : Immutables et remplaçables
5. **Domain Events** : Pour communication entre agrégats
6. **Repository Pattern** : Abstraction de la persistence
7. **Domain Services** : Pour logique ne relevant pas d'une seule entité

## Anti-patterns à éviter

❌ Anemic Domain Model (modèles sans logique)
❌ Getters/Setters systématiques
❌ Logique métier dans les controllers/use cases
❌ Dépendances du domaine vers l'infrastructure
❌ Entités avec constructeur vide
❌ Validation en dehors du domaine
❌ Tests sans assertions ou trop génériques
