# Gong cha Click & Collect · thème enfant Storefront

SAé 3.01 (BUT MMI 2, IUT du Limousin) · Gabriel Trottmann-Barale · projet étudiant, site non officiel.

## Installation (développement ou production)

```bash
cd wp-content/themes
git clone https://github.com/gabtb974-spec/sae301-gongcha-child.git gongcha-child
wp theme activate gongcha-child
```

Prérequis : WordPress, WooCommerce, Storefront, Classic Editor, Secure Custom Fields,
PDF Invoices & Packing Slips for WooCommerce (IT-17).

## Structure

| Fichier | Rôle | Itération |
|---|---|---|
| `style.css` | En-tête du thème enfant (`Template: storefront`) | IT-1 |
| `functions.php` | Styles (après ceux de Storefront et de WooCommerce), puis `inc/*.php` | IT-1 |
| `assets/css/tokens.css` | Variables de la charte (mêmes valeurs que Figma et le Design System) | IT-1 |
| `assets/css/theme.css` | Styles du site, un bloc commenté par itération | IT-2 → |
| `inc/setup.php` | Réglages Storefront et WooCommerce (grilles, accueil, pied de page) | IT-2 |
| `inc/variations.php`, `assets/js/variations.js` | Sélecteur d'options en pastilles, prix | IT-3 |
| `inc/options-simples.php` | Options gratuites des produits simples (panier, commande) | IT-3 (audit) |
| `inc/champs-produit.php`, `acf-json/group_gc_infos_produit.json` | Champs SCF : composition, allergènes, valeurs nutritives | IT-4 |
| `inc/panier.php`, `assets/js/quantite.js` | Panier : suggestions, boutons − / + | IT-5 |
| `inc/retrait.php`, `assets/js/retrait.js` | Instructions pour le retrait (réglables dans le back-office) | IT-6 |
| `inc/compte.php` | Espace client | IT-7 |
| `inc/emails.php`, `woocommerce/emails/` | E-mails aux couleurs de l'enseigne | IT-8 |
| `inc/commandes.php`, `inc/classes/` | État « Prête à retirer », annulation client | IT-9 |
| `inc/equipe.php`, `archive-membre.php`, `single-membre.php` | Équipe (type de contenu + gabarits) | IT-11 |
| `inc/collections.php` | Taxonomie Collection | IT-12 |
| `inc/ventes-croisees.php` | Produits suggérés et complémentaires | IT-13 |
| `inc/avis.php` | Avis clients | IT-14 |
| `inc/frais-preparation.php` | Frais de préparation sous un seuil | IT-15 |
| `front-page.php`, `inc/accueil.php` | Page d'accueil | IT-16 |
| `inc/produit-star.php` | Produit le plus vendu identifié automatiquement | W |
| `inc/factures.php`, `woocommerce/pdf/gongcha/` | Modèle de facture PDF | IT-17 |
| `docs/TESTS-IT*.md` | Scénarios de test par itération | |

Les réglages en base (codes promo, zones d'expédition, etc.) ne sont pas dans ce dépôt :
ils passent de Dev à Prod par l'export / import de la base de données (US-1.4).

## Convention de commits

Chaque commit commence par le tag de l'itération, par exemple `[IT-3] Produits variables…`,
puis l'itération terminée est marquée : `git tag IT-3 && git push && git push --tags`.
