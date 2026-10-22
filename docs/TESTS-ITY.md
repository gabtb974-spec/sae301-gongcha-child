# Itération Y · Audit et performances (Lighthouse)

## US-Y.1 · Audit
Rapports dans `docs/lighthouse/` (HTML lisible, JSON brut), page testée : fiche du produit star.
- `produit-mobile.report.html` : mobile, réseau 4G lent simulé (réglage par défaut de Lighthouse) ;
- `produit-desktop.report.html` : ordinateur.
Relancer : `bash outils/lighthouse.sh` depuis le dossier du thème (ou Chrome › DevTools › Lighthouse › Navigation, catégories Performance, Accessibilité, Bonnes pratiques, SEO).

## US-Y.2 · Correctifs appliqués
| Problème Lighthouse | Correctif | Fichier |
|---|---|---|
| Ressources qui bloquent l'affichage (Google Fonts, serveur tiers) | Polices hébergées sur le site, sous-ensemble latin, préchargées, `font-display: swap` | `assets/fonts/`, `assets/css/polices.css`, `functions.php`, `inc/performance.php` |
| CSS inutilisé (blocs Gutenberg, WooCommerce Blocks) | Styles de blocs retirés sur les pages sans blocs | `inc/performance.php` |
| Images trop lourdes / formats anciens | Tailles réduites générées en WebP, images régénérées | `inc/performance.php` + `wp media regenerate` |
| LCP tardif (image produit) | `fetchpriority="high"`, pas de chargement différé sur l'image principale | `inc/performance.php` |
| Image originale chargée pour la loupe | Loupe au survol désactivée (visionneuse au clic conservée) | `inc/performance.php` |
| Scripts émojis | Retirés | `inc/performance.php` |
| Requête répétée (produit star) | Cache WordPress (transient 12 h, vidé à chaque commande/modification de produit) | `inc/produit-star.php` |
| Cache navigateur, compression | Bloc `mod_expires` / `mod_deflate` dans `.htaccess` (production Apache) ; CSS/JS versionnés par date de modification | `.htaccess`, `functions.php` |
| SEO « page bloquée » | Visibilité moteurs de recherche activée | Réglages › Lecture |

## US-Y.3 · Scores obtenus
Voir le tableau affiché par le kit et les rapports. Objectif : ≥ 80 dans les 4 catégories.
En local, la performance dépend de la machine (DDEV, Docker) : refaire l'audit en production sur mmi.unilim.fr pour le rendu final.
