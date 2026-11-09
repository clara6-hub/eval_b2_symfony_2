# Évaluation Symfony n°2 – Agenda d'événements : tests et nouvelles fonctionnalités

Application de gestion d'événements (concerts, conférences, ateliers, sport) avec Symfony 7.4.

Ce dépôt contient **la correction de l'évaluation n°1** (événements, inscriptions, comptes et validation fonctionnent déjà) et un **environnement de test prêt à l'emploi** (fixtures, base de test, `make tests`). Dans cette évaluation, vous allez :

1. ajouter la page « Mes inscriptions », un bouton **Refuser** dans l'admin et la **modification** d'un événement ;
2. écrire des **tests unitaires** et des **tests fonctionnels** avec PHPUnit.

Le design est **déjà fait** : le HTML des nouveautés est en dur dans les templates, à vous de le rendre dynamique **sans vous occuper du CSS**.

Référence : *Symfony : The Fast Track*, chapitre 17 « Tester » : <https://symfony.com/doc/7.4/the-fast-track/fr/17-tests.html>

---

## ⚠️ Avant de commencer : forkez le dépôt

> **Ne commitez et ne poussez JAMAIS sur ce dépôt.** Vous travaillez uniquement sur **un nouveau fork** de ce dépôt.
>
> Ne reprenez **pas** votre fork de l'évaluation n°1 (`eval_b2_symfony`) et ne le supprimez pas. L'évaluation n°2 est un **autre dépôt**, qui part de la correction de l'évaluation n°1.

1. Rendez-vous sur <https://github.com/louvelmathieu/eval2_b2_symfony> et cliquez sur le bouton **Fork** en haut à droite.

   > Votre fork doit être **public** : c'est lui qui sera corrigé. Utilisez bien le bouton **Fork** (le fork d'un dépôt public est public par défaut) et ne faites pas de copie privée du projet.

2. Clonez **votre fork** (et non le dépôt d'origine), dans un **nouveau dossier** :

   ```bash
   git clone https://github.com/<votre-compte>/eval2_b2_symfony.git
   cd eval2_b2_symfony
   ```

3. Vérifiez que `origin` pointe bien vers **votre** compte :

   ```bash
   git remote -v
   ```

4. Commitez régulièrement sur votre fork, avec des messages clairs.

---

## 📦 Rendu

Le rendu se fait sur **votre fork** (branche `main`). Avant la date limite, complétez ce bloc et commitez-le :

```text
Nom       : Kameni
Prénom    : Clara
```

La correction se fera en lançant `make tests` : **tous les tests doivent passer** (`OK` en vert). Inutile de commiter une base de données : les fixtures la recréent.

---

## Installation

### Prérequis

- PHP **8.3** ou plus, avec l'extension `pdo_sqlite` (`php -m | grep sqlite` pour vérifier)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download) (recommandé)
- `make` (sous Windows : `scoop install make`)

### Étapes

```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load -n   # remplit la base dev avec les données de test
symfony server:start
```

Le site est accessible sur <http://127.0.0.1:8000>. Sans Symfony CLI : `php -S 127.0.0.1:8000 -t public`

### Base de données

Le projet utilise **SQLite** : chaque environnement a son propre fichier.

| Environnement | Fichier | Utilisé par |
|---|---|---|
| `dev` | `var/data_dev.db` | le site dans votre navigateur |
| `test` | `var/data_test.db` | PHPUnit (`APP_ENV=test`, défini dans `phpunit.dist.xml`) |

> `doctrine:database:create` ne fonctionne pas avec SQLite et n'est pas nécessaire : `doctrine:migrations:migrate` crée le fichier.

---

## Ce qui est fourni

| Fichier | Contenu |
|---|---|
| `src/`, `templates/` | La correction de l'évaluation n°1 |
| `src/DataFixtures/AppFixtures.php` | Les données de test (voir ci-dessous) |
| `Makefile` | `make tests` : recrée la base de test, charge les fixtures et lance PHPUnit |
| `phpunit.dist.xml`, `config/bundles.php` | DAMA DoctrineTestBundle, déjà installé et configuré |
| `templates/event/mine.html.twig` | HTML statique de la page « Mes inscriptions » |
| `templates/base.html.twig` | Lien « Mes inscriptions » du menu, en commentaire |
| `templates/validate/index.html.twig` | Bouton « Refuser », en commentaire |
| `templates/event/show.html.twig` | Bouton « Modifier », en commentaire |
| `templates/event/edit.html.twig` | Page de modification, à compléter avec le formulaire |

### L'environnement de test

```bash
make tests                           # recrée la base de test et lance tous les tests
php bin/phpunit --filter testHome    # relance un seul test (sans recréer la base)
```

`make tests` supprime `var/data_test.db`, rejoue les migrations, charge les fixtures puis lance PHPUnit. Relancez-le après chaque modification de vos entités ou des fixtures.

**DAMA** annule à la fin de chaque test tout ce qu'il a écrit en base : chaque test repart des fixtures, sans dépendre des autres.

### Les données de test (fixtures)

| Email | Mot de passe | Rôle |
|---|---|---|
| `admin@test.fr` | `admin123` | `ROLE_ADMIN` |
| `user@test.fr` | `user123` | – |

| Événement | Date | Catégorie | Places | Validé | Inscrits | Auteur |
|---|---|---|---|---|---|---|
| Soirée jazz | dans 10 jours | concert | 80 | oui | – | user (à décommenter au point 5) |
| Semi-marathon | dans 15 jours | sport | 300 | oui | user | – |
| Initiation à la poterie | dans 20 jours | atelier | 1 | oui | admin (→ complet) | – |
| Concert passé | il y a 10 jours | concert | 80 | oui | – | – |
| Tournoi de basket | dans 30 jours | sport | 10 | **non** | – | – |

> Ne modifiez pas les fixtures : les tests s'appuient sur ces données.

---
## Sujet

---

## Astuces

### Commandes utiles

```bash
php bin/console make:test          # choisir TestCase ou WebTestCase
php bin/console doctrine:fixtures:load -n         # recharge les fixtures en dev
make tests                                        # lance tous les tests
php bin/phpunit --filter testHome                 # lance un seul test
```

### Tests fonctionnels

```php
// Se connecter sans passer par le formulaire
$user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user@test.fr']);
$client->loginUser($user);

// Retrouver l'id d'un événement des fixtures
$event = static::getContainer()->get(EventRepository::class)->findOneBy(['title' => 'Soirée jazz']);
$client->request('GET', '/evenement/'.$event->getId().'/modifier');

// Compter des éléments
$crawler = $client->request('GET', '/');
$this->assertCount(3, $crawler->filter('.event-card'));

// Envoyer un fichier dans un formulaire : donnez son chemin
'event[image]' => dirname(__DIR__, 2).'/public/images/sport.jpg',
```

- Assertions utiles : `assertResponseIsSuccessful()`, `assertResponseStatusCodeSame(404)`, `assertResponseRedirects('/login')`, `assertSelectorTextContains()`, `assertSelectorExists()`, `assertSelectorNotExists()`, `assertAnySelectorTextContains()`.
- Après un `submitForm` qui redirige, pensez à `$client->followRedirect()` avant de vérifier la page.
- Revenir à la page précédente dans un contrôleur : `return $this->redirect($request->headers->get('referer'));`. Le `Referer` est une URL complète : dans les tests, vérifiez avec `assertResponseRedirects('http://localhost/mes-inscriptions')`.
- Le nom du bouton passé à `submitForm()` est son **texte** : `'Refuser'`, `'Se désinscrire'`, `'Enregistrer l\'événement'`…

### Tests unitaires

Un test unitaire n'utilise ni la base ni Symfony : on crée les objets à la main.

```php
$event = new Event();
$event->setPlaces(10);
$event->addParticipant(new User());

$this->assertSame(9, $event->getRemainingPlaces());
```
