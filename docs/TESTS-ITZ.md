# Itération Z · Traduction des extensions

## US-Z.1 · Toutes les extensions en français
Le kit installe les traductions françaises officielles (translate.wordpress.org) du cœur, de toutes les extensions et des thèmes,
puis affiche le statut de chacune (`installed` = traduction présente).
Thème enfant : textes écrits en français, domaine `gongcha-child`, modèle `languages/gongcha-child.pot` pour d'autres langues.

## US-Z.2 · Interface dans la langue du visiteur
Langue du site et du compte administrateur : français. Contrôle : boutique, fiche, panier, validation, Mon compte, e-mails, facture PDF :
aucun texte anglais (« Add to cart », « Invoice », « Billing details »…).

## US-Z.3 · Modifier les traductions
Extension **Loco Translate**. Vocabulaire Click & Collect déjà adapté (fichier « personnalisé » qui passe avant la traduction officielle
et survit aux mises à jour, source versionnée dans `languages/surcharges/woocommerce-fr_FR.po`) :

| Texte d'origine | Traduction officielle | Gong cha |
|---|---|---|
| Billing details | Détails de facturation | Vos coordonnées |
| Proceed to checkout | Valider la commande | Passer commande |
| Thank you. Your order has been received. | Merci. Votre commande a été reçue. | Merci ! Votre commande est transmise à la boutique… |

Modifier : Loco Translate › Extensions › WooCommerce › Français (emplacement « Personnalisé ») › chercher le texte › Enregistrer.
Scénario : changer « Passer commande » en « Je commande », enregistrer, recharger le panier › le bouton a changé.
