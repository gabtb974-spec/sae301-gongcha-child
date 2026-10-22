#!/usr/bin/env bash
# Mise en production Dev (DDEV) → Prod (mmi.unilim.fr) · US-1.4 et US-1.5.
# Procédure détaillée et explications : docs/MISE-EN-PRODUCTION.md
#
#   bash outils/deployer.sh premiere   # première installation (US-1.4)
#   bash outils/deployer.sh            # mise à jour après une itération (code + extensions + médias + base)
#   bash outils/deployer.sh code       # mise à jour du thème seulement (git pull), base de production conservée
#
# Chaque déploiement réussi ajoute une ligne à docs/JOURNAL-MEP.md (journal des mises en production).
set -euo pipefail
MODE="${1:-maj}"
THEME="$(cd "$(dirname "$0")/.." && pwd)"
SITE="$(cd "$THEME/../../.." && pwd)"
ENV_FILE="$THEME/outils/deploiement.env"
[ -f "$ENV_FILE" ] || { echo "Copier outils/deploiement.env.exemple en outils/deploiement.env et le compléter."; exit 1; }
# shellcheck source=/dev/null
. "$ENV_FILE"
PROD_PORT="${PROD_PORT:-22}"
PROD_URL="${PROD_URL%/}"
DEPOT="$(git -C "$THEME" remote get-url origin)"

etape() { echo; echo "== $*"; }
mkdir -p "$HOME/.ssh"
SSH_OPTS=(-p "$PROD_PORT" -o ControlMaster=auto -o "ControlPath=$HOME/.ssh/gongcha-%r@%h-%p" -o ControlPersist=15m)
distant() { ssh "${SSH_OPTS[@]}" "$PROD_SSH" "$@"; }
envoyer() { rsync -az --delete -e "ssh ${SSH_OPTS[*]}" "$@"; }

cd "$SITE"
ddev describe >/dev/null 2>&1 || ddev start
DEV_URL="$(ddev wp option get home)"
PREFIXE="$(ddev wp config get table_prefix)"
VERSION="$(git -C "$THEME" describe --tags --always)"

etape "Connexion à $PROD_SSH (mot de passe demandé une seule fois)"
distant "echo '  Connecté à' \$(hostname) '· PHP' \$(php -r 'echo PHP_VERSION;')"

etape "WP-CLI sur le serveur"
WPR="$(distant 'if command -v wp >/dev/null 2>&1; then command -v wp; else mkdir -p ~/bin; [ -x ~/bin/wp ] || { curl -fsSL -o ~/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x ~/bin/wp; }; echo ~/bin/wp; fi')"
distant "$WPR --version" | sed 's/^/  /'
WP="$WPR --path=$PROD_DIR"

if [ "$MODE" = "premiere" ]; then
	etape "1/4 Dernière version de WordPress (fr_FR) dans ~/$PROD_DIR"
	distant "mkdir -p $PROD_DIR && { [ -f $PROD_DIR/wp-load.php ] || $WP core download --locale=fr_FR; }" | sed 's/^/  /'
	etape "2/4 wp-config.php (configuration manuelle, US-1.4)"
	if ! distant "test -f $PROD_DIR/wp-config.php"; then
		cat <<TXT
  wp-config.php n'existe pas encore. Sur le serveur :
    ssh -p $PROD_PORT $PROD_SSH
    cd $PROD_DIR && cp wp-config-sample.php wp-config.php && nano wp-config.php
  Renseigner DB_NAME, DB_USER, DB_PASSWORD, DB_HOST (base MySQL de mmi.unilim.fr),
  les clés de https://api.wordpress.org/secret-key/1.1/salt/ et le préfixe :
    \$table_prefix = '$PREFIXE';
  Puis relancer : bash outils/deployer.sh premiere
TXT
		exit 1
	fi
	distant "grep -q \"table_prefix *= *'$PREFIXE'\" $PROD_DIR/wp-config.php" \
		|| { echo "  ⚠ Le préfixe de wp-config.php doit être '$PREFIXE' (celui de la base de Dev)."; exit 1; }
	echo "  wp-config.php présent, préfixe $PREFIXE ✓"
	etape "3/4 Thème enfant depuis GitHub"
	distant "cd $PROD_DIR/wp-content/themes && { [ -d gongcha-child/.git ] || git clone -q $DEPOT gongcha-child; }" && echo "  Cloné depuis $DEPOT"
	etape "4/4 .htaccess (réécriture d'adresses + cache)"
	BASE="$(echo "$PROD_URL" | sed -E 's#^https?://[^/]+##')/"
	{ cat <<HT
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase $BASE
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . ${BASE}index.php [L]
</IfModule>
# END WordPress
HT
	  sed -n '/# BEGIN Gong cha/,/# END Gong cha/p' "$SITE/.htaccess"; } | distant "cat > $PROD_DIR/.htaccess"
	echo "  RewriteBase $BASE"
fi

etape "Thème enfant : dernière version de GitHub ($VERSION)"
git -C "$THEME" push -q origin HEAD --tags
distant "cd $PROD_DIR/wp-content/themes/gongcha-child && git pull -q --ff-only && git fetch -q --tags && git describe --tags --always" | sed 's/^/  Prod : /'

if [ "$MODE" != "code" ]; then
	etape "Storefront, extensions, traductions, médias (rsync : seuls les fichiers modifiés partent)"
	envoyer wp-content/themes/storefront/ "$PROD_SSH:$PROD_DIR/wp-content/themes/storefront/"
	envoyer wp-content/plugins/ "$PROD_SSH:$PROD_DIR/wp-content/plugins/"
	envoyer wp-content/languages/ "$PROD_SSH:$PROD_DIR/wp-content/languages/"
	envoyer --exclude 'wpo_wcpdf*/attachments' wp-content/uploads/ "$PROD_SSH:$PROD_DIR/wp-content/uploads/"
	echo "  ✓"

	etape "Base de données Dev → Prod"
	if [ "$MODE" != "premiere" ]; then
		read -r -p "  La base de production va être remplacée par celle de Dev (une sauvegarde est faite avant). Continuer ? (o/N) " ok
		[ "$ok" = "o" ] || { echo "  Annulé."; exit 1; }
	fi
	mkdir -p "$SITE/.mep"
	DATE="$(date +%Y%m%d-%H%M)"
	ddev wp db export ".mep/dev-$DATE.sql" --quiet
	distant "mkdir -p ~/mep && { $WP core is-installed 2>/dev/null && $WP db export ~/mep/prod-avant-$DATE.sql --quiet && echo '  Sauvegarde prod : ~/mep/prod-avant-$DATE.sql' || true; }"
	rsync -az -e "ssh ${SSH_OPTS[*]}" ".mep/dev-$DATE.sql" "$PROD_SSH:mep/"
	distant "$WP db import ~/mep/dev-$DATE.sql --quiet \
		&& $WP search-replace '$DEV_URL' '$PROD_URL' --all-tables-with-prefix --skip-columns=guid --report-changed-only --format=count \
		&& $WP search-replace '${DEV_URL//\//\\/}' '${PROD_URL//\//\\/}' --all-tables-with-prefix --skip-columns=guid --format=count >/dev/null \
		&& $WP rewrite flush && $WP cache flush >/dev/null && rm ~/mep/dev-$DATE.sql" | sed 's/^/  Adresses remplacées : /'
	rm -f "$SITE/.mep/dev-$DATE.sql"
fi

etape "Vérification"
distant "$WP option get home; $WP theme list --status=active --field=name" | sed 's/^/  /'
CODE_ACCUEIL="$(curl -s -o /dev/null -w '%{http_code}' "$PROD_URL/")"
CODE_ADMIN="$(curl -s -o /dev/null -w '%{http_code}' "$PROD_URL/wp-login.php")"
echo "  Accueil $CODE_ACCUEIL · Connexion back-office $CODE_ADMIN"

etape "Journal des mises en production"
JOURNAL="$THEME/docs/JOURNAL-MEP.md"
echo "| $(date '+%d/%m/%Y %H:%M') | $VERSION | $(git -C "$THEME" rev-parse --short HEAD) | $MODE | accueil $CODE_ACCUEIL, admin $CODE_ADMIN | $PROD_URL |" >> "$JOURNAL"
git -C "$THEME" add docs/JOURNAL-MEP.md
git -C "$THEME" commit -q -m "[IT-1] Journal des mises en production : $VERSION ($MODE)"
git -C "$THEME" push -q origin HEAD
distant "cd $PROD_DIR/wp-content/themes/gongcha-child && git pull -q --ff-only" || true
echo "  Ligne ajoutée à docs/JOURNAL-MEP.md et poussée."
echo
echo "Production : $PROD_URL · back-office : $PROD_URL/wp-admin/"
