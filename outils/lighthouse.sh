#!/usr/bin/env bash
# Itération Y · Audit Lighthouse de la fiche du produit star (mobile + ordinateur), rapports dans docs/lighthouse/.
# Usage : bash outils/lighthouse.sh
# Avec Node.js : Lighthouse en local. Sans Node.js : Lighthouse dans un conteneur Docker (Docker est déjà là pour DDEV).
set -euo pipefail
THEME="$(cd "$(dirname "$0")/.." && pwd)"
SITE="$(cd "$THEME/../../.." && pwd)"
URL="$(cd "$SITE" && ddev wp eval 'echo get_permalink( gongcha_produit_star() );')"
OUT="$THEME/docs/lighthouse"
mkdir -p "$OUT"
echo "  Page auditée : $URL"

SCORES='
	const r = require(process.argv[1]); const c = r.categories;
	const s = k => Math.round(c[k].score * 100);
	const ligne = ["performance","accessibility","best-practices","seo"].map(k => `${c[k].title} ${s(k)}`).join(" · ");
	const bas = Object.keys(c).filter(k => s(k) < 80);
	console.log(`  ${process.argv[2].padEnd(8)} ${ligne}${bas.length ? "   ⚠ sous 80 : " + bas.join(", ") : "   ✓"}`);'
FLAGS='--quiet --only-categories=performance,accessibility,best-practices,seo --output=html --output=json'

if command -v npx >/dev/null; then
	for mode in mobile desktop; do
		extra=""; [ "$mode" = desktop ] && extra="--preset=desktop"
		npx -y lighthouse@12 "$URL" $FLAGS $extra --chrome-flags="--headless=new --ignore-certificate-errors" --output-path="$OUT/produit-$mode"
		node -e "$SCORES" "$OUT/produit-$mode.report.json" "$mode"
	done
	exit 0
fi

command -v docker >/dev/null || { echo "  Ni Node.js ni Docker : audit à faire dans Chrome › DevTools › Lighthouse"; exit 1; }
if ! docker image inspect gongcha-lighthouse >/dev/null 2>&1; then
	echo "  Préparation de l'image Docker Lighthouse (une seule fois, 2 à 4 min)…"
	docker build -q -t gongcha-lighthouse - >/dev/null <<'DOCKERFILE'
FROM node:20-bookworm-slim
RUN apt-get update && apt-get install -y --no-install-recommends chromium fonts-dejavu-core ca-certificates \
 && rm -rf /var/lib/apt/lists/* && npm install -g lighthouse@12 --no-fund --no-audit
ENV CHROME_PATH=/usr/bin/chromium
DOCKERFILE
fi
HOTE="$(echo "$URL" | sed -E 's#^https?://([^/:]+).*#\1#')"
ROUTEUR="$(docker inspect -f '{{(index .NetworkSettings.Networks "ddev_default").IPAddress}}' ddev-router)"
docker run --rm --network ddev_default --add-host "$HOTE:$ROUTEUR" -v "$OUT:/out" -e URL="$URL" -e FLAGS="$FLAGS" -e SCORES="$SCORES" \
	gongcha-lighthouse sh -c '
	for mode in mobile desktop; do
		extra=""; [ "$mode" = desktop ] && extra="--preset=desktop"
		lighthouse "$URL" $FLAGS $extra --chrome-flags="--headless=new --no-sandbox --disable-dev-shm-usage --ignore-certificate-errors" --output-path=/out/produit-$mode || exit 1
		node -e "$SCORES" /out/produit-$mode.report.json $mode
	done'
