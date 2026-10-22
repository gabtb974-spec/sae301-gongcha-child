# IT-21 · Scénario de test du référencement

1. **US-21.1** — Back-office › Produits › « Thé au lait noir avec perles » : bloc « Référencement (IT-21) »
   avec Balise Title (60 car.) et Meta description (155 car.). Idem dans Produits › Catégories › « Thé au lait ».
2. **US-21.2** — Sur la fiche produit, clic droit › Afficher le code source › rechercher `og:` :
   `<title>`, `meta name="description"`, `og:title`, `og:description`, `og:image`, `og:url`, `og:type` = product,
   `product:price:amount`, `twitter:card`.
   En ligne de commande : `curl -sk https://sae301-gongcha.ddev.site/?p=<id> | grep -E "<title>|description|og:"`
3. Modifier la meta description dans le back-office › le code source change.
4. Produit sans champ rempli (ex. Thé vert à la pêche) › titre « Nom | Gong cha Click & Collect » et description courte.
5. **US-21.3** — En production (site public), tester le partage avec https://www.opengraph.xyz ou le
   « Sharing Debugger » de Facebook : l'aperçu montre l'image, le titre et la description.
   Réglages › Lecture › « Demander aux moteurs de recherche de ne pas indexer » doit être décoché.
