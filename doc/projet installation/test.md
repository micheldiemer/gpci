# Installer le projet

Voici les étapes pour installer le projet

## Ce qui faut télécharger

- Télécharger la version la plus recente de virtual box

![Alt text](img/virtual-box.png)

- Télécharger l'iso ubuntu server version **14.04** (cet version précisement)

![Alt text](img/serveur-ubuntu-14-04.png)

- Télécharger Visual Studio code

![Alt text](img/vscode.png)

## Installation du serveur  

- Ouvré Virtual Box et cliquer sur **Nouvelle**

![Alt text](img/interface-virtualbox.png)

- Cliqué sur la barre déroulante **ISO Image:**
- Cherhcé votre iso ubuntu
- Changer le **Nom :** par celui que vous voulez
![Alt text](img/choisir-iso.png)

- Des losanges rouges avec un point d'éxlamation devrait s'afficher dans l'onglet Unattended Install
- Modifier le **Hostname**, **Username** (doit être en minuscule) et le **Password**

> Dans les anciennes version de virtualbox **Username** et le **Password** se faisait pendant l'installation du server

- Cliquer sûr **Finish**
![Alt text](img/changer-identifiant.png)

## Entrer dans le serveur  

Cliquer sûr **Démarrer**
![Alt text](img/demarrer-serveur.png)

Aprés avoir démarrer entrer vos identifiants
![Alt text](img/identifiant-seveur.png)

Changer le réseau de votre votre serveur de **Nat** en **Accès par ponts**
![Alt Text](gif/changer-reseau.gif)

## Partager les fichier avec SFTP

Installer Git

![Alt Text](img/git.png)

Installer l'exention SFTP de "Natizyskunk"

![Alt Text](gif/extension-sftp.gif)

Ouvrer le projet gpci ou cloner le dépot si vous ne l'avez pas encore fait

![Alt Text](gif/cloner-projet.gif)

Appuyer sur la combinaison de touche **ctrl+shift+p**
Taper **sftp**
Changer ces information du fichier sftp.json:

![Alt Text](gif/sftp.gif)

```json
{
    "name": "Nom que vous voulez",
    "host": "l'addresse ip de votre serveur",
    "protocol": "sftp",
    "port": 22,
    "username": "username de votre serveur",
    "username": "mot de passe de votre serveur",
    "remotePath": "/",
    "uploadOnSave": false,
    "useTempFile": false,
    "openSsh": false
}
```
