# IT-18 · Scénario de test de la recherche et des filtres

1. **La carte** (ordinateur) : barre latérale à droite avec « Filtrer par prix », « Taille », « Collections », « Catégories ».
2. **Prix** : déplacer les curseurs (ex. 5 € – 6 €) › Filtrer › seuls les produits dans cette fourchette restent ;
   la pastille « × Prix min / max » apparaît dans « Filtres actifs ».
3. **Taille** : cocher « Large » › seules les boissons proposées en Large restent ; l'adresse contient `filter_taille=large`.
4. Combiner prix + taille + une catégorie › les filtres se cumulent.
5. Filtre sans résultat (prix très bas) › « Aucun produit… » + lien « Réinitialiser les filtres ».
6. **Recherche** : barre de recherche de l'en-tête › « perles » › résultats produits.
7. **Mobile** (Inspecter › téléphone) : bouton « Filtrer » au-dessus de la grille › volet plein écran ›
   « Voir les résultats » ou Échap le referme.
8. Panier, fiche produit, compte : toujours en pleine largeur (pas de barre latérale vide).
