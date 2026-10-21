# IT-17 · Scénario de test des factures PDF

1. **US-17.1** — Passer une commande (retrait, Au retrait). Elle passe à « En cours ».
   - Attendu : l'e-mail « Commande n° … confirmée » (Mailpit : `ddev mailpit`) contient la pièce jointe `facture-GC-2026-0001.pdf`.
   - Back-office › Commandes : colonne « Facture » avec le numéro ; bouton PDF « Facture » dans la commande.
2. Passer une seconde commande › numéro **GC-2026-0002** (numérotation continue, sans trou).
3. **US-17.2** — Ouvrir le PDF : bandeau rouge avec le logo, nom et coordonnées de l'enseigne, titre « Facture » rouge,
   tableau à en-tête rouge, totaux, mention de retrait, pied de page avec SIRET et mention TVA (fictifs).
4. **US-17.3** — Client connecté › Mon compte › Commandes › bouton **Facture PDF** › le PDF s'ouvre.
   - Le bouton n'apparaît que lorsque la facture existe (commande confirmée).

Mentions présentes : numéro unique et séquentiel, date de facture et de commande, identité et adresse du vendeur,
SIRET et TVA, identité et adresse du client, désignation, quantité, prix, total, mode de paiement, mention TVA.
