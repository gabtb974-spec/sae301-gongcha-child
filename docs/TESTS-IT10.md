# IT-10 · Scénarios de test des promotions

| Code | Type | Portée | Résultat attendu |
|---|---|---|---|
| `perles20` | −20 % | Thé au lait noir avec perles, Thé au lait de taro | Remise sur ces 2 boissons seulement |
| `mousse1` | −1,00 € par article | Thé noir et Thé Oolong avec mousse de lait | 1 € de moins par boisson concernée |
| `bienvenue10` | −10 % | Tout le panier, non cumulable | Remise sur le total |
| `ete2026` | −15 % | Tout | Refusé : « Ce code promo a expiré. » |
| `flash5` | −5 € | Tout | Refusé : limite d'utilisation atteinte |
| `xyz` | — | — | Refusé : le code n'existe pas |

## US-10.1 (admin)
Marketing › Codes promo : vérifier les 4 codes, leur type et l'onglet « Restriction d'usage » (2 produits pour `perles20` et `mousse1`).

## US-10.2 (affichage)
- La carte : badge rond « Promo ! » sur le Thé noir avec mousse de lait et le Thé au lait noir avec perles, ancien prix barré.
- Panier : message vert « Code promo appliqué avec succès », ligne « Code promo : … » en vert avec « Retirer ».
- Erreur : message rouge.

## US-10.3 (client)
1. Panier avec un Thé au lait noir avec perles + un Thé vert à la mangue. Saisir `perles20` → remise sur la première boisson seulement.
2. « Retirer » le code → total d'origine.
3. Panier avec seulement le Thé vert à la mangue, saisir `perles20` → refusé (produit non concerné).
4. Saisir `ete2026` → refusé (expiré). Saisir `flash5` → refusé (épuisé). Saisir `xyz` → refusé (inexistant).
5. Réappliquer un code valide et passer commande : la remise apparaît dans la commande et dans l'e-mail.
