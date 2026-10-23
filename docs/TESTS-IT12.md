# IT-12 · Taxonomie Collection : scénarios de test

1. **US-12.1** — Back-office › Produits › Collections : les collections se créent, se renomment et se décrivent ici,
   indépendamment des catégories. Dans une fiche produit, cases à cocher « Collections » ; colonne « Collections » dans la liste des produits.
2. **US-12.2** — 3 collections (Fruité et frais, Gourmand, Sans lait), chacune avec au moins 2 produits.
3. **US-12.3** — Menu › **Collections** : la page `/collections/` affiche **toutes les collections en même temps**,
   chacune avec son bandeau (nom, description, couleur) et la grille de ses produits. Les pastilles du haut mènent à chaque collection.
   « Voir la collection … » ouvre la page dédiée à une seule collection (`/collection/<nom>/`).
4. Fiche produit : la ligne « Collection : … » renvoie vers la page de la collection.
