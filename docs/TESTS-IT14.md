# IT-14 · Scénario de test des avis

Aucun avis n'est inventé : les avis du site viennent de commandes de test réelles.

1. Navigation privée, **non connecté** : ouvrir une fiche, onglet « Avis ».
   - Attendu : carte crème « Seuls les clients connectés ayant acheté ce produit ont la possibilité de laisser un avis. »
2. Se connecter avec le **compte client de test**, commander le Thé noir avec mousse de lait (retrait, Au retrait).
3. Back-office : passer cette commande à **Terminée** (un avis n'est possible qu'après une commande payée).
4. Client : revenir sur la fiche › onglet « Avis » › choisir une note › écrire un commentaire › **Soumettre**.
   - Attendu : avis enregistré (en attente si la modération est active).
5. Back-office : **Produits › Avis** › approuver l'avis.
   - Attendu sur la fiche : pastille avec l'initiale, prénom, « propriétaire vérifié », date, étoiles rouges, commentaire ;
     sous le titre du produit, la note moyenne et « (1 avis client) » ; l'onglet devient « Avis (1) ».
6. Essayer de noter un produit **non acheté** avec le même compte → formulaire remplacé par le message de restriction.
