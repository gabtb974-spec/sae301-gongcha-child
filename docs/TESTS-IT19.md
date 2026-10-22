# IT-19 · Scénario de test des tarifs selon le poids

Poids : Regular 0,55 kg, Large 0,75 kg. Tranches par défaut : ≤ 1 kg (+0 €), ≤ 3 kg (+2 €), au-delà (+4,50 €).
Tarif d'une livraison = tarif de la zone + supplément. Le retrait en boutique reste gratuit.

| Panier | Poids | Tranche | Livraison express 24 h (9,90 €) | Vélo Paris (3,90 €) |
|---|---|---|---|---|
| 1 Regular | 0,55 kg | 1 | 9,90 € | 3,90 € |
| 2 Regular | 1,10 kg | 2 | 11,90 € | 5,90 € |
| 3 Large | 2,25 kg | 2 | 11,90 € | 5,90 € |
| 6 Regular | 3,30 kg | 3 | 14,40 € | 8,40 € |

1. **US-19.3** — Panier : ligne « Poids de la commande » au-dessus des modes d'expédition, tarifs affichés avant la validation.
2. **US-19.1** — Changer les quantités (tableau ci-dessus) › Mettre à jour › le tarif de livraison suit la tranche.
3. **US-19.2** — WooCommerce › Réglages › Expédition › Options d'expédition › « Tarifs selon le poids » :
   passer la tranche 2 à +3 € › Enregistrer › le panier de 2 Regular affiche 12,90 € en express.
4. Le retrait en boutique reste à 0 € dans tous les cas ; la commande enregistre le poids dans la ligne d'expédition.
