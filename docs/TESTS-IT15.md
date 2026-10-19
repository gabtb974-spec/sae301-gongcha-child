# IT-15 · Scénario de test des frais de préparation

Réglages par défaut : 0,50 € de frais sous 10,00 € de panier (codes promo déduits).

1. Panier avec **1 Thé noir avec mousse de lait** (4,50 €).
   - Attendu : ligne « Frais de préparation 0,50 € » ; bandeau « Plus que 5,50 € pour ne plus payer les frais de préparation. »
2. Passer la quantité à **3** (13,50 €) › Mettre à jour le panier.
   - Attendu : la ligne de frais disparaît ; bandeau vert « Pas de frais de préparation sur ce panier. »
3. Revenir à **2** (9,00 €) › les frais réapparaissent.
4. Panier à 3 articles (13,50 €) + code `bienvenue10` (−1,35 € → 12,15 €) › pas de frais.
   Avec un panier à 10,50 € + `bienvenue10` (→ 9,45 €) › les frais réapparaissent (seuil calculé après remise).
5. Back-office : WooCommerce › Réglages › Expédition › Options d'expédition : seuil **15** et montant **0,80** › Enregistrer.
   - Attendu : le panier à 13,50 € affiche maintenant 0,80 € de frais.
6. Décocher « Activer » › plus aucun frais, plus de bandeau.
