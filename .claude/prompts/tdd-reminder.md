# Prompt de rappel TDD/DDD

## Comportement par défaut

Pour TOUTE demande de développement, l'assistant DOIT systématiquement :

### 1. Analyser le besoin métier
- Identifier les concepts du domaine
- Déterminer les Value Objects nécessaires
- Identifier les Entités et Agrégats
- Repérer les Services du domaine

### 2. Créer les tests AVANT le code (Phase RED)
```
"Je vais d'abord écrire les tests unitaires pour cette fonctionnalité"
```

- Créer les fichiers de tests dans tests/Unit/Domain/
- Écrire tous les cas de test (nominal + erreurs + limites)
- Exécuter les tests pour vérifier qu'ils échouent
- Montrer les résultats : "✗ Tests échouent comme attendu (le code n'existe pas encore)"

### 3. Implémenter le code minimal (Phase GREEN)
```
"Maintenant j'implémente le code pour faire passer les tests"
```

- Créer les classes dans src/Domain/
- Utiliser PHP 8.2 avec types stricts
- Code minimal sans sur-engineering
- Exécuter les tests pour vérifier qu'ils passent
- Montrer les résultats : "✓ Tous les tests passent"

### 4. Refactorer (Phase REFACTOR)
```
"Je refactorise pour améliorer la qualité"
```

- Éliminer les duplications
- Améliorer la lisibilité
- Appliquer les principes SOLID
- Vérifier après chaque modification que les tests passent
- Montrer : "✓ Tests toujours verts après refactoring"

### 5. Documenter (Phase DOC)
```
"J'ajoute la documentation complète"
```

- PHPDoc sur toutes les classes
- PHPDoc sur toutes les méthodes publiques
- Description métier et patterns DDD
- Exemples d'utilisation si pertinent

### 6. Vérification finale
```bash
composer test          # Tous les tests passent
composer test:coverage # Couverture > 80%
composer analyse       # Pas d'erreurs PHPStan
```

## Exemples de réponses attendues

### Exemple 1 : Nouvelle entité

```
User: "Ajoute une entité Tournoi"