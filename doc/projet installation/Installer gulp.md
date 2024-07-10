# Utilisation de gulp

## Installation de nvm, nodejs et gulp

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
nvm install lts/dubnium
# nodejs v10.24.1 compatible avec Ubuntu 14.04
nvm use lts/dubnium
# cf. https://gulpjs.com/docs/en/getting-started/quick-start/
npm install --save-dev gulp
```

## Installation des modules node

```bash
# après avoir git clone et cd gpci
npm install
```

## Création des dossiers pour la mise en production

```bash
# après avoir git clone et cd gpci
# cf. fichier gulpfile.js
mkdir -p ./production/webApp ./production/webApp/app/views/ ./production/webApp/app/modals/ ./production/css ./production/webApp/backend
# attention aux fichiers app.js et app.dev.js 
gulp webApp
```



