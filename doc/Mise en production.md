# Mise en production

## Créer la base de données

- Voir le dossier `db/` pour les fichiers sql et créer un utilisateur admin.
- Créer la base de données et/ou exécuter les migrations préparées

## Copier les fichiers nécessaires

- Installer `nodejs`, `gulp`, etc.
- Voir le fichier `gulpfile.js`
- Exécuter la commande `gulp`
- Voir dossier `preprod/webApp` 

## Ajuster le dossier `webApp`

- Voir dossier `preprod/webApp` 
- Vérifier dans `app.js` la valeur de `const BASE_URL`
- Vérifier les valeurs dans `backend/settings.php`

## Configurer le serveur

- Serveur apache : mod rewrite activé, acceptation du fichier `.htaccess`
- Droits d'écriture dans le dossier `backend/uploads/`
- Configurer `backend/settings.php`
- Déployer le dossier `preprod/webApp` sur le serveur de production
   
