# IT-20 · Scénario de test des créneaux

Réglages par défaut : 11:00 – 21:00, retrait par tranches de 15 min (au plus tôt 20 min après la commande),
livraison par plages d'une heure (au plus tôt 1 h après), 6 jours proposés.

1. **US-20.3 (retrait)** — Panier › Retrait en boutique › Valider la commande :
   ligne « Jour et créneau de retrait » avec la liste des jours (« Aujourd'hui, … », « Demain, … ») et des quarts d'heure.
   Les créneaux déjà passés du jour n'apparaissent pas.
2. Changer de jour › la liste des créneaux se recharge (premier créneau à 11:00 pour demain).
3. **US-20.1 / 20.2 (livraison)** — Choisir une livraison › le titre devient « Jour et créneau de livraison »,
   les créneaux deviennent des plages d'une heure (11:00 – 12:00…).
4. Commander › page « Commande reçue » : « Créneau de retrait : Demain, … · 12:30 – 12:45 » en pastille lavande.
   Le créneau figure aussi dans l'e-mail (Mailpit), la commande du back-office, Mon compte › commande et la facture PDF.
5. Réglages : fermeture à 19:00 › Enregistrer › plus aucun créneau après 19:00.
