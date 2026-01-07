# Skill : Workflow TDD/DDD

**Nom du skill** : `tdd-workflow`

**Description** : Guide l'assistant à travers le workflow complet TDD/DDD pour toute nouvelle fonctionnalité

## Usage

```
/tdd-workflow <description de la fonctionnalité>
```

## Comportement

Ce skill force l'assistant à suivre rigoureusement le processus TDD/DDD :

### Phase 1 : Analyse métier (UNDERSTAND)

L'assistant DOIT :
1. Reformuler le besoin métier
2. Identifier les concepts du domaine
3. Proposer la structure DDD :
   - Quels Value Objects ?
   - Quelles Entités ?
   - Quels Services du domaine ?
   - Dans quel Bounded Context ?

**Output attendu** :
```
📋 Analyse métier :
- Besoin : [reformulation]
- Concepts identifiés : [liste]
- Value Objects : [liste avec justification]
- Entités : [liste avec justification]
- Services : [liste si nécessaire]
- Bounded Context : [nom du contexte]
```

### Phase 2 : Tests d'abord (RED)

L'assistant DOIT :
1. Créer les fichiers de tests dans `tests/Unit/Domain/`
2. Écrire TOUS les tests avant le code :
   - Cas nominal
   - Cas d'erreur
   - Cas limites
   - Tests de validation
3. Exécuter les tests avec `composer test`
4. Vérifier qu'ils échouent (car le code n'existe pas)

**Output attendu** :
```
🔴 Phase RED - Tests écrits :
✓ tests/Unit/Domain/ValueObject/LicenceTest.php
  - test_should_create_licence_when_valid
  - test_should_throw_exception_when_invalid_format
  - test_should_compare_equality

Résultat : ✗ 8 tests, 8 failures (attendu - le code n'existe pas)
```

### Phase 3 : Implémentation minimale (GREEN)

L'assistant DOIT :
1. Créer les classes dans `src/Domain/`
2. Implémenter le code MINIMAL pour faire passer les tests
3. Utiliser PHP 8.2 avec types stricts
4. Exécuter `composer test` après chaque classe
5. Ne pas sur-engineer

**Output attendu** :
```
🟢 Phase GREEN - Implémentation :
✓ src/Domain/ValueObject/Licence.php créé
✓ src/Domain/Model/Joueur.php créé

Résultat : ✓ 8 tests passent (8/8)
```

### Phase 4 : Refactoring (REFACTOR)

L'assistant DOIT :
1. Identifier les améliorations possibles :
   - Duplication de code
   - Violations SOLID
   - Lisibilité
2. Refactorer étape par étape
3. Exécuter les tests après CHAQUE modification
4. S'arrêter quand le code est clean

**Output attendu** :
```
🔵 Phase REFACTOR - Améliorations :
✓ Extraction méthode validateFormat() dans Licence
✓ Application du pattern Factory pour création
✓ Renommage pour clarté métier

Résultat : ✓ 8 tests passent toujours (8/8)
```

### Phase 5 : Documentation (DOC)

L'assistant DOIT :
1. Ajouter PHPDoc complet sur toutes les classes
2. Documenter toutes les méthodes publiques
3. Expliquer les patterns DDD utilisés
4. Ajouter des exemples d'utilisation

**Output attendu** :
```
📚 Phase DOC - Documentation :
✓ PHPDoc complet sur Licence
✓ PHPDoc complet sur Joueur
✓ README.md du contexte mis à jour
✓ Exemples d'utilisation ajoutés
```

### Phase 6 : Vérification qualité (VERIFY)

L'assistant DOIT exécuter :
```bash
composer test           # Tests unitaires
composer test:coverage  # Couverture > 80%
composer analyse        # PHPStan niveau 9
composer cs:check       # Standards PSR-12
```

**Output attendu** :
```
✅ Vérification qualité :
✓ Tests : 8/8 passent
✓ Couverture : 92% (> 80%)
✓ PHPStan : 0 erreur (niveau 9)
✓ Code style : PSR-12 respecté

🎉 Fonctionnalité terminée et validée
```

## Checklist de fin

L'assistant doit vérifier :
- [ ] Tous les tests passent
- [ ] Couverture > 80%
- [ ] Aucune erreur PHPStan niveau 9
- [ ] Code respecte PSR-12
- [ ] PHPDoc complet
- [ ] Principes DDD respectés
- [ ] Aucune dépendance externe dans le domaine
- [ ] Value Objects immuables
- [ ] Entités protègent leurs invariants

## Exemple d'utilisation

```
User: /tdd-workflow Ajoute la possibilité de calculer le taux de victoire d'un joueur