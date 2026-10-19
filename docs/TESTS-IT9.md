# IT-9 · Scénarios de test des commandes

Chaque e-mail se lit dans Mailpit (`ddev mailpit`). Notez le résultat (OK / KO + remarque) pour l'auto-évaluation.

## US-9.2 · Panier multiple, retrait en boutique, paiement sur place
1. Navigation privée. Ajouter **3 produits différents** au panier, dont une boisson avec options.
2. Panier : choisir **Retrait en boutique**. Valider la commande, paiement **Au retrait**, Commander.
   - Attendu : page « Commande reçue » avec les instructions de retrait ; e-mail « Commande n° … confirmée » ; état **En cours**.
3. Back-office › WooCommerce › Commandes : passer la commande à **Prête à retirer**.
   - Attendu : e-mail « Votre commande n° … est prête à retirer » avec le montant à régler sur place.
4. Passer la commande à **Terminée** (retirée et payée au comptoir).
   - Attendu : e-mail « Commande n° … retirée ».

## US-9.3 · Compte client, livraison, virement, annulation, remboursement
1. Se connecter avec le compte client de test. Commander **1 produit simple**, mode **Livraison**, paiement **Par virement**.
   - Attendu : état **En attente** ; e-mail « reçue : en attente de votre virement ».
2. Back-office : passer la commande à **En cours** (virement reçu).
   - Attendu : e-mail « Commande n° … confirmée » (confirmation du paiement et de la préparation).
3. Client › Mon compte › Commandes : bouton **Annuler**.
   - Attendu : message de confirmation ; état **Annulée** ; e-mails « Commande annulée » (client et admin).
4. Back-office : ouvrir la commande › **Rembourser** › montant total › « Rembourser manuellement ».
   - Attendu : état **Remboursée** ; e-mail « Commande n° … remboursée ».

## US-9.1 · Statuts et stock
1. Noter le stock d'une variation suivie (ex. Thé au lait noir avec perles · Regular · Perles en plus = 3).
2. Commander 1 de cette variation → stock 2 (le stock baisse dès « En attente » / « En cours »).
3. Annuler cette commande → stock remonté à 3 (WooCommerce rétablit le stock à l'annulation).
4. Commander la quantité restante → la variation passe **en rupture** et n'est plus sélectionnable sur la fiche.
