��    C      4  Y   L      �     �     �  "   �            
   %     0     H     T  /   a     �     �     �     �     �  S   �  �   -    �  S   �     @	     M	     Z	  	   m	  	   w	     �	     �	     �	     �	     �	  �   �	  {   �
  �   �
  �   �  f   H  �   �     7  +   C     o     �  �   �     D     T     `     l      �     �     �  B   �  +   "     N     l     p  3   �  h   �  .   %     T     h     �     �  	   �     �     �  	   �     �     �     �            +   $  "   P     s     �  
   �  &   �     �     �  4   �     3     E     e     y     �  `   �  �     V  �  i   4     �     �  )   �                "  !   :     \     m     q    z  �   �  �   %  �     �   �  �   l     �  ?        A     Y  �   e     :     I     [     k  1   �  '   �  @   �  C   #  4   g  ,   �     �  &   �  5   �  �   *  8   �     �           3      D      [   
   g      r      �      �   	   �      �                1           -   	           %                 )           3   (   '      
      ?           2                    *             >   :   &       .   7                    4            /   9                <   =   @   A      ;   ,      B   8             "      5      6       +   0       C   #      $          !        %s - Access restricted Access denied redirect URL Access to this site is restricted. Add to database Authentication CAS Client CAS authenticated users CAS version CAS.php path Configuration settings for WordPress CAS Client Default role Disable CAS logout E-mail Suffix Everyone Feature disabled If you aren't automatically redirected, please click on <a href='%s'>this link</a>. If you choose to allow access only to <em>CAS authenticated users</em>, the user will be authenticated using CAS and authenticated in Wordpress only if he already has an account. If you choose to allow access only to <em>Wordpress authenticated users</em>, the user will be authenticated using CAS and authenticated in Wordpress if he already has an account or if you choose to allow adding user in database. Otherwise, the access will be denied. If you choose to allow access to <em>everyone</em>, no restriction will be applied. LDAP Base DN LDAP Bind DN LDAP Bind password LDAP Host LDAP Port LDAP attribut for %s LDAP attributes mapping LDAP parameters No Note Note: If you disable CAS logout, when a user click on the logout link, he will only be logged off from Wordpress, not from the CAS server (and potential other CAS authenticated services). Note: The phpCAS library is required for this plugin to work. We need to know the server absolute path to the CAS.php file. Note: This default role is only used to create the user on its first connection. Afterwards, the user role could be configured in Wordpress and will not be overwritten from LDAP. Note: This suffix is used to constitute user email if it couldn't be retreived from LDAP. You must only enter the email domain name (without the '@'). Note: Using Javascript to redirect user to CAS login page enables to keep hashtag in URL (if present). Now that you've activated this plugin, WordPress is attempting to authenticate using CAS, even if it's not configured or misconfigured. Please wait Redirect to CAS login page using Javascript Restrict site access to Save Save yourself some trouble, open up another browser or use another machine to test logins. That way you can preserve this session to adjust the configuration or deactivate the plugin. Server Hostname Server Path Server Port Site access restriction Sorry, this feature is disabled. Treatment of unregistered users Use LDAP to get user info WARNING : The path to CAS.php file currently defined is incorrect! WordPress CAS Client plugin not configured. Wordpress authenticated users Yes You are now logged off. You can restrict access to the public website here: You have to configure here which LDAP attributes could be mapped with Wordpress user profil information. You will be redirected soon to the login page. phpCAS include path phpCAS::client() parameters the affiliations the alternative email the email the first name the last name the login the nice name the nickname the role Project-Id-Version: 
POT-Creation-Date: 
PO-Revision-Date: 
Last-Translator: 
Language-Team: 
Language: fr_FR
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Generator: Poedit 1.8.11
Plural-Forms: nplurals=2; plural=(n > 1);
 %s - Accès restreint URL de redirection en cas d'accès interdit L'accès à ce site est restreint. Ajouter à la base de données Authentification Client CAS aux utilisateurs authentifiés via CAS Version de CAS Chemin du fichier CAS.php Paramètres de configuration de WordPress CAS Client Rôle par défaut Désactiver la déconnexion CAS Suffixe du courriel Tout le monde Fonctionnalité désactivée Si vous n'êtes pas automatiquement redirigé(e), merci de cliquer sur <a href='%s'>ce lien</a>. Si vous choisissez d'autoriser l'accès uniquement <em>aux utilisateurs authentifiés via CAS</em>, l'utilisateur sera authentifié via CAS puis via Wordpress uniquement s'il y possède déjà un compte utilisateur. Si vous choisissez d'autoriser l'accès uniquement <em>aux utilisateurs authentifiés par Wordpress</em>, l'utilisateur sera authentifié via CAS puis via Wordpress s'il y possède déjà un compte utilisateur ou si vous avez choisi d'autoriser l'ajout des utilisateurs non-inscrits dans la base de données. Autrement, l'accès sera refusé. Si vous choisissez d'autoriser l'accès à <em>tout le monde</em>, aucune restriction ne sera appliquée. Base DN du serveur LDAP DN de connexion au serveur LDAP Mot de passe de connexion au serveur LDAP Serveur LDAP Port du serveur LDAP L'attribut LDAP pour %s Correspondance des attributs LDAP Paramètres LDAP Non Remarque Remarque : Si vous désactivez la déconnexion CAS, quand l'utilisateur cliquera sur le lien de déconnexion, il sera déconnecté uniquement au niveau de WordPress, et non au niveau du serveur CAS (et potentiellement des autres services utilisant l'authentification CAS). Remarque : La librairie phpCAS est indispensable pour que ce plugin fonctionne. Vous devez connaître le chemin absolu du fichier CAS.php sur le serveur. Remarque : Ce rôle par défaut est uniquement utilisé pour créer l'utilisateur lors de sa première connexion. Par la suite, le rôle de l'utilisateur peut-être configuré dans Wordpress et ne sera écrasé depuis le LDAP. Remarque : Ce suffixe est utilisé pour composer le courriel de l'utilisateur si celui-ci n'a pu être récupéré depuis LDAP. Vous devez saisir uniquement le nom de domaine de messagerie (sans le '@'). Remarque : Utiliser Javascript pour rediriger l'utilisateur vers la page de connexion CAS permet de conserver l'hashtag dans l'URL (s'il est présent). Maintenant que ce plugin est activé, WordPress tente d'authentifier en utilisant CAS, même si il n'est pas (ou mal) configuré. Merci de patienter Rediriger vers la page de connexion CAS en utilisant Javascript Restreindre l'accès à Enregistrer Il est recommandé de tester la connexion depuis un autre navigateur ou un autre ordinateur. De cette façon, vous pouvez conserver cette session pour ajuster la configuration ou désactiver le plug-in au besoin. Nom du serveur Chemin du serveur Port du serveur Restriction d'accès au site Désolé, cette fonctionnalité est désactivée. Traitement des utilisateurs non-inscris Utiliser LDAP pour récupérer les informations de l'utilisateur ATTENTION : Le chemin du fichier CAS.php renseigné est incorrect ! Le plugin Wordpress CAS Client n'est pas configuré. aux utilisateurs authentifiés via Wordpress Oui Vous êtes maintenant déconnecté(e). Vous pouvez restreindre l'accès au site public ici : Vous devez configurer ici quels attributs LDAP peuvent être utilisés pour renseigner les informations du profil de l'utilisateur dans Wordpress. Vous allez être redirigé(e) vers la page de connexion. Chemin d'inclusion de phpCAS Paramètres de phpCAS::client() les affiliations le courriel alternatif le courriel le prénom le nom de famille l'identifiant le nom d'affichage le surnom le rôle 