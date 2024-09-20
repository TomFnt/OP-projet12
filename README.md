# Projet Symfony 6.4 - API EcoGarden & co

EcoGarden API est une API développé sous Symfony 6.4 qui permet de fournir des conseils de jardinage, ainsi que la prévision météo pour une ville choisie par l'utilisateur.

## Prérequis

Avant de pouvoir installer et exécuter ce projet, assurez-vous d'avoir les prérequis suivants installés sur votre système :

- PHP 8.1 ou supérieur
- Composer (gestionnaire de dépendances PHP)
- Serveur de base de données compatible (par exemple, MySQL)
- L'application Postman si vous souhaitez les routes plus précisément.

## Installation

### 1. Installation de votre  projet symfony

Pour créer un nouveau projet Symfony 6.4, utilisez la commande suivante afin d'installer votre projet Symfony avec Composer :

```bash
composer create-project symfony/skeleton:"6.4.*" ecogardenAPI
````
Maintant, vous devez vous positionnez dans le répertoire de votre projet.

### 2. Cloner le repos Github

Afin de pouvoir récupérer directement les modifications disponnible sur le repo GitHub du projet, Initialisez un nouveau dépôt Git à la racine de votre projet Symfony en utilisant cette commande à la racine de votre projet Symfony :
```bash
git init 
````

Ensuite, Ajoutez le repo GitHub grâce à la commande suivante :
```bash
git remote add origin https://github.com/TomFnt/projet12.git
````

Enfin, il ne reste plus qu'à récupèrer les modifications du repoo distant directement sur votre branche main en local avec la commande suivante :
```bash
git pull origin main
```

### 3. Installer les dépendances

Une fois les modifications du repos distant récupérés sur votre environnement. Éxecuter la commande suivante afin d'installer l'ensemble des dépendances néccessaire au fonctionnement du projet.
```bash
composer install
```

### 4. Configurer la base de données


Avant de créer la base de donnée, vous devait créer votre propre fichier ".env.local" à la racine du projet et configurez votre base de données :

```plaintext
mysql://root:@127.0.0.1:3306/ecogarden?serverVersion=8&charset=utf8mb4
```
Remplacez user et password par votre nom d'utilisateur et mot de passe de base de données.

Ensuite, exécuter les commandes suivantes afin de créer la base de donnée.
```bash
php bin/console doctrine:database:create
```

```bash
php bin/console doctrine:migrations:migrate
```

### 7. Ajout de la clé d'api pour OpenWeatherMap

Dans votre fichier ".env.local", vous devez indiqué la clé fournis par OpenWeatherMap de la manière suivante : 

```plaintext
### Open Weahther Map API Key###
OPENWEATHER_API_KEY= your_api_key
```

### 8. Charger les données de test (facultatif)
```bash
php bin/console doctrine:fixtures:load
```
Cette commande ajoutera des données de test à votre base de donnée. Votre jeu test de donnée contiendra alors 9 comptes utilisateurs ainsi que 24 conseils.


## Utilisation

Pour exécuter l'application, utilisez la commande Symfony CLI suivante :

```bash
symfony server:start
```

## Documentation des différentes routes

Une documentation spécifique des différentes routes est disponnibles grâce au package Nelmio. Si vous êtes en local, vous pouvez accèder à la documentation de cette façon : 
* http://localhost:8000/api/doc

Dans le cas où vous ajoutez cette api directement sur un de vos serveur avec votre propre nom de domaine, vous aurez juste à rajouter le endpoint suivant "api/doc".
* Exemple : /api/doc