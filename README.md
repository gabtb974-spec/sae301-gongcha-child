# Gong cha Click & Collect · thème enfant Storefront

SAé 3.01 (BUT MMI 2) · projet étudiant, site non officiel.

## Installation

```bash
cd wp-content/themes
git clone https://github.com/[ton-compte]/gongcha-child.git
wp theme activate gongcha-child      # ou : ddev wp theme activate gongcha-child
```

Prérequis : WordPress, WooCommerce, Storefront, Classic Editor, Secure Custom Fields.

## Structure

| Fichier | Rôle |
|---|---|
| `style.css` | En-tête du thème enfant (Template: storefront) |
| `functions.php` | Chargement des styles, puis de `inc/*.php` |
| `assets/css/tokens.css` | Variables de la charte (mêmes noms que dans Figma) |
| `assets/css/theme.css` | Styles du thème |
| `inc/` | Fonctions par itération (créneau de retrait, frais, etc.) |

## Convention de commits

Chaque commit commence par le tag de l'itération : `[IT-1] Création du thème enfant`.
Une fois l'itération terminée : `git tag IT-1 && git push --tags`.
