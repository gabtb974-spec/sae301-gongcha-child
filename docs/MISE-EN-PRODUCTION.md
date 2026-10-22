# Mise en production · Dev (DDEV) → Prod (mmi.unilim.fr)

US-1.4 (premier déploiement) et US-1.5 (procédure de mise à jour, journal).
Le script `outils/deployer.sh` enchaîne exactement les étapes décrites ici ; chaque étape peut aussi être faite à la main.

## Ce qui passe de Dev à Prod, et comment

| Élément | Où il vit | Transfert |
|---|---|---|
| Thème enfant `gongcha-child` | Dépôt GitHub | `git pull` sur le serveur (jamais de copie à la main) |
| Thème parent Storefront, extensions | `wp-content/themes/storefront`, `wp-content/plugins` | `rsync` (seuls les fichiers modifiés) |
| Traductions (dont Loco Translate) | `wp-content/languages` | `rsync` |
| Médias (photos produits, logo, factures) | `wp-content/uploads` | `rsync` |
| Produits, réglages WooCommerce, pages, menus, codes promo… | Base de données | export Dev → import Prod, puis changement d'adresse avec WP-CLI via SSH |
| Cœur WordPress, `wp-config.php` | Serveur uniquement | installé et configuré sur le serveur (jamais copié depuis Dev) |

## Prérequis
- Accès SSH à mmi.unilim.fr (hors du campus : VPN de l'université si nécessaire) et une base MySQL (nom, utilisateur, mot de passe).
- `outils/deploiement.env` rempli à partir de `outils/deploiement.env.exemple` (fichier non versionné, sans mot de passe).
- Sur le Mac : DDEV démarré, dépôt du thème à jour (`git status` propre).

## 1. Premier déploiement (US-1.4) · `bash outils/deployer.sh premiere`
1. **WordPress** : dernière version française téléchargée sur le serveur (`wp core download --locale=fr_FR`).
   WP-CLI est installé dans `~/bin/wp` s'il n'existe pas sur le serveur.
2. **wp-config.php, à la main** : `cp wp-config-sample.php wp-config.php` puis `nano wp-config.php` :
   `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST` de la base mmi.unilim.fr, clés de sécurité
   (https://api.wordpress.org/secret-key/1.1/salt/) et **le même préfixe de tables qu'en Dev** (`$table_prefix`).
   Le script s'arrête tant que ce fichier n'existe pas, puis vérifie le préfixe.
3. **Thème enfant** : `git clone` du dépôt GitHub dans `wp-content/themes/gongcha-child`.
4. **.htaccess** : règles de réécriture WordPress avec le bon `RewriteBase` (site dans un sous-dossier) et bloc cache/compression de l'itération Y.
5. Puis les étapes communes ci-dessous (fichiers, base, vérification, journal).

## 2. Mise à jour après une itération (US-1.5) · `bash outils/deployer.sh`
1. Commit et tag de l'itération en Dev (`[IT-N] …`, `git tag IT-N`).
2. `git push` puis, sur le serveur, `git pull` du thème enfant.
3. `rsync` de Storefront, des extensions, des traductions et des médias.
4. **Base de données** (confirmation demandée) :
   1. sauvegarde de la base de production : `wp db export ~/mep/prod-avant-DATE.sql` ;
   2. export de Dev : `ddev wp db export` ;
   3. import en production : `wp db import` ;
   4. changement d'adresse **via SSH** : `wp search-replace 'https://sae301-gongcha.ddev.site' 'URL-de-prod' --all-tables-with-prefix --skip-columns=guid`
      (WP-CLI corrige aussi les données sérialisées), puis la même chose avec les `/` échappés (`https:\/\/…`) pour les données JSON ;
   5. `wp rewrite flush` et `wp cache flush`.
5. Vérification : adresse du site, thème actif, codes HTTP de l'accueil et de la page de connexion.
6. Une ligne est ajoutée à `docs/JOURNAL-MEP.md`, committée et poussée.

Variante : `bash outils/deployer.sh code` ne met à jour que le thème (aucune donnée de production écrasée),
pour un correctif de CSS ou de PHP.

## Retour en arrière
- Thème : `git checkout IT-N` dans `wp-content/themes/gongcha-child` sur le serveur (chaque itération a son tag).
- Base : `wp db import ~/mep/prod-avant-DATE.sql` (sauvegarde faite avant chaque import).

## Après la mise en production
- Se connecter au back-office de production et vérifier : boutique, fiche produit, panier, validation de commande, Mon compte, e-mails.
- Refaire l'audit Lighthouse sur l'adresse de production.
- Réglages › Permaliens › Enregistrer si une page renvoie une erreur 404.
