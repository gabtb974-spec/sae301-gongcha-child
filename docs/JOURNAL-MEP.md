# Journal des mises en production

Chaque exécution de `outils/deployer.sh` ajoute une ligne (US-1.5). Procédure : `docs/MISE-EN-PRODUCTION.md`.
Le développement des itérations 1 à 22, W, X, Y et Z a été fait en Dev (DDEV) ; la première mise en production
regroupe donc toutes ces itérations (version indiquée par le tag Git).

| Date | Version (tag) | Commit | Type | Vérification HTTP | Adresse |
|---|---|---|---|---|---|
| 23/10/2026 16:45 | IT-Z-9-g97cccc3 | 97cccc3 | première installation, manuelle (FTP + phpMyAdmin) | accueil 200, 18 liens de l'accueil 200, back-office 200 · Lighthouse fiche produit : mobile 72/100/100/100 (passages 61/72/81), ordinateur 97/100/100/100 | https://mmi.unilim.fr/~trottmannbara1/Sites/sae301-gongcha/ |

Notes de la première installation :
- Fichiers envoyés par FTP ; `wp-config.php` de production rédigé à la main (base `trottmannbara1`, hôte `localhost`, préfixe `r3sae01_`, nouvelles clés), `wp-config-ddev.php` non utilisé.
- Base : export local avec remplacement d'adresse (`wp search-replace … --export=base-production.sql`, 41 remplacements, 53 tables), import dans phpMyAdmin.
- Le serveur ignore les fichiers `.htaccess` des dossiers personnels : permaliens réglés sur « Simple » (`?p=`, `?page_id=`), lien de menu « L'équipe » corrigé en `?post_type=membre`.
- Lighthouse en production (local : mobile 94) : temps de réponse du serveur 670 ms et pas d'en-têtes de cache (`.htaccess` ignoré), d'où le score mobile plus bas.
- Cache de pages (WP Super Cache) non installable : WordPress demande des identifiants FTP, le serveur ne lui permet pas d'écrire dans ses propres fichiers.
