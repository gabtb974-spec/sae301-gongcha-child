# Itération X · Sécurité des formulaires (anti-spam)

Solution choisie : **champ piège + jeton horodaté signé** (`inc/anti-spam.php`), sans extension ni captcha.

| Contrôle | Ce qui est bloqué | Effet pour le visiteur |
|---|---|---|
| Champ piège `gc_champ_piege` (hors écran, `aria-hidden`, `tabindex="-1"`) | Robots qui remplissent tous les champs | Aucun : invisible, ignoré au clavier et par les lecteurs d'écran |
| Jeton `gc_jeton` = horodatage + signature `wp_hash()` | Envois directs sans afficher le formulaire, jetons fabriqués | Aucun |
| Délai minimal 2 s, validité 24 h | Scripts qui soumettent instantanément, jetons réutilisés | Message clair, il suffit de renvoyer |

Formulaires protégés : inscription « Mon compte » (WooCommerce), inscription WordPress, avis produits (et commentaires).
Non concernés : réponses de l'administration, utilisateurs pouvant modérer.

## Pourquoi pas reCAPTCHA / hCaptcha / Turnstile ?
US-X.3 (pas de captcha illisible) ; pas de script tiers (performance, itération Y) ni de transfert de données à un tiers (RGPD, politique de confidentialité de l'IT-22).
Limite assumée : un robot qui exécute le JavaScript et attend 2 s passe le contrôle. Si le spam devenait réel en production,
on ajouterait Cloudflare Turnstile (invisible) ; WooCommerce limite déjà les avis aux acheteurs vérifiés (IT-14).

## Scénarios
1. **US-X.3** — Navigation privée › Mon compte › S'enregistrer avec un vrai e-mail › compte créé, aucune étape en plus.
2. **US-X.2** — Inspecter le formulaire : le champ « Ne remplissez pas ce champ » et `gc_jeton` existent mais rien n'est visible. Tab ne passe pas dessus.
3. **US-X.1** — Inspecter › remplir `gc_champ_piege` à la main › S'enregistrer › message « Votre envoi n'a pas pu être vérifié… », pas de compte créé.
4. **US-X.1** — Laisser un avis (compte client ayant acheté) › avis enregistré. Avec le champ piège rempli › page « Avis non envoyé » (403).
5. Tableau de bord › bloc « Anti-spam des formulaires » : le compteur a augmenté avec le motif.
6. Automatique : le kit simule 4 envois (sans jeton, piège rempli, envoi en 0 s, visiteur normal) et un faux avis envoyé par `curl` (attendu : 403).
