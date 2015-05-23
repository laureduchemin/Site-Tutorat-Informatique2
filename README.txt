Ce site web a pour objectif d’aider les étudiants dans leur réussite en licence. Les tuteurs sont des étudiants en licence qui mettent leurs compétences mathématiques et informatiques au service des étudiants en difficultés.


*Pour prendre notre site Web et le mettre sur votre serveur, voici comment procéder* 


#INSTALLATION DES LOGICIELS REQUIS

1. Pour les utilisateurs de Windows, télécharger Mamp ou Wamp allez sur leur site respectif.
Télécharger Mamp si vous êtes sur Mac Os X  et Lamp si vous êtes sur Linux.

2. Installer le logiciel correspondant à votre système d'exploitation.




 #INSTALLATION GENERALE


1.Télécharger l'archive du site web à l'adresse : 

https://github.com/laureduchemin/Site-Tutorat-Informatique2/archive/master.zip

2.Décompresser l'archive à l'aide de winrar ou winzip

3-Vous obtenez un répertoire nommé "Site-Tutorat-Informatique2". 

4. Récupérer le fichier tib.sql disponible dans le sous repertoire BDD du répertoire nommé "Site-Tutorat-Informatique2".
 Ce fichier est indispensable au bon fonctionnement du site.

5. Lancer le logiciel Mamp/Lamp ou Wamp

6. Ouvrir votre navigateur Web préféré, et taper l'adresse suivante "http://localhost/phpmyadmin". 
**Par défaut: l'identidiant est "root" et  le password est "root"  pour Mac Os X et Linux **
**et aucun password pour Windows**
Vous pourrez modifier votre mot de passe une fois connecté.

7. Création d'une base de données "tib" :
Toujours à l'adresse "http://localhost/phpmyadmin" c'est -à- dire sur phpmyadmin
Cliquer sur nouvelle base de données en haut à gauche
   Il y a deux champs, dans le premier qui est vide écrire "tib"( nommer votre base "tib") 
Dans le menu déroulant avec écrit "Interclassement" choisir "utf8_general_ci" vers le bas de la liste. 
Ensuite Valider, la base de données est créée. 

8. Importation de la base de données:
Dans la suite vous aurez à importer la base de données du site web.

Cliquer sur le nom de votre base de données tib dans le menu vertical à gauche ensuite cliquez sur le bouton "importer" dans le menu horizontal en haut.
Puis cliquer sur parcourir et aller chercher le fichier tib.sql disponible dans le sous repertoire BDD du répertoire nommé "Site-Tutorat-Informatique2"
Une fois fait, cliquer sur importer, votre base de données  est ainsi complet.



9.Ouvrir et modifier le fichier "Site-Tutorat-Informatique2\configuration.php" à l'aide d'un éditeur de texte comme "bloc-note" ou "notepad++" par le fichier configurationMac.php pour les utilisateurs de Mac ou Linux et  par le fichier configurationWindows.php pour les utilisateur de windows

   *Pour les utilisateurs de Windows*
     1. Ajouter les paramètres de votre base de données 
   *Explication* :
   (Remplacer : localhost, root, mdp et mabd par les paramètres de votre base de données )   

   
   
         $db_host = "localhost";      // <------ Adresse de la base de données (hote)
         $db_user = "root";  // <-------------- Utilisateur de la base de données
         $db_pass = "mdp";         // <--------- Mot de passe de la base de données
         $db =      "mabd";       // <--------- Nom de la base de données (tib) 

      2.  enregistrer le fichier.
  


# BESOIN D'AIDE 
Si vous avez des questions ou pour plus d’informations, vous pouvez toujours nous contacter à l'adresse suivante:
responsable.tutorat@gmail.com'


Copyright: 
 * Copyright (C) 2015. Tous droits réservés *




