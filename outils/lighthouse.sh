#!/usr/bin/env bash
# Itération Y · Audit Lighthouse de la fiche du produit star, rapports dans docs/lighthouse/.
# Mobile : 3 passages, on garde le rapport médian (méthode recommandée par Lighthouse, les mesures varient d'un passage à l'autre).
# Ordinateur : 1 passage.
# Usage : bash outils/lighthouse.sh   (Node.js local si présent, sinon conteneur Docker, déjà installé pour DDEV)
set -euo pipefail
THEME="$(cd "$(dirname "$0")/.." && pwd)"
SITE="$(cd "$THEME/../../.." && pwd)"
URL="$(cd "$SITE" && ddev wp eval 'echo get_permalink( gongcha_produit_star() );')"
OUT="$THEME/docs/lighthouse"
mkdir -p "$OUT"
echo "  Page auditée : $URL"

# Script exécuté avec Lighthouse disponible : $1 = commande lighthouse, $2 = dossier de sortie, $3 = drapeaux Chrome
AUDIT='
set -e
LH="$1"; O="$2"; CF="$3"
F="--quiet --only-categories=performance,accessibility,best-practices,seo --output=html --output=json"
for i in 1 2 3; do $LH "$URL" $F --chrome-flags="$CF" --output-path="$O/.m$i" >/dev/null 2>&1 || exit 1; done
$LH "$URL" $F --preset=desktop --chrome-flags="$CF" --output-path="$O/produit-desktop" >/dev/null 2>&1 || exit 1
node -e "
const fs=require(\"fs\"),o=process.argv[1];
const runs=[1,2,3].map(i=>({i,r:JSON.parse(fs.readFileSync(o+\"/.m\"+i+\".report.json\"))}));
runs.sort((a,b)=>a.r.categories.performance.score-b.r.categories.performance.score);
const med=runs[1].i;
for(const ext of [\"json\",\"html\"]) fs.renameSync(o+\"/.m\"+med+\".report.\"+ext,o+\"/produit-mobile.report.\"+ext);
for(const f of fs.readdirSync(o)) if(f.startsWith(\".m\")) fs.unlinkSync(o+\"/\"+f);
const ligne=(n,r)=>{const c=r.categories,s=k=>Math.round(c[k].score*100);
 const b=Object.keys(c).filter(k=>s(k)<80);
 console.log(\"  \"+n.padEnd(9)+[\"performance\",\"accessibility\",\"best-practices\",\"seo\"].map(k=>c[k].title+\" \"+s(k)).join(\" · \")+(b.length?\"   ⚠ sous 80 : \"+b.join(\", \"):\"   ✓\"));};
console.log(\"  Mobile, performance des 3 passages : \"+runs.map(x=>Math.round(x.r.categories.performance.score*100)).join(\" / \")+\" → médiane retenue\");
ligne(\"mobile\",runs[1].r);
ligne(\"desktop\",JSON.parse(fs.readFileSync(o+\"/produit-desktop.report.json\")));
" "$O"
'

if command -v npx >/dev/null; then
	URL="$URL" bash -c "$AUDIT" _ "npx -y lighthouse@12" "$OUT" "--headless=new --ignore-certificate-errors"
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
echo "  4 passages (3 mobile + 1 ordinateur), environ 2 min…"
docker run --rm --network ddev_default --add-host "$HOTE:$ROUTEUR" -v "$OUT:/out" -e URL="$URL" -e AUDIT="$AUDIT" \
	gongcha-lighthouse bash -c 'bash -c "$AUDIT" _ lighthouse /out "--headless=new --no-sandbox --disable-dev-shm-usage --ignore-certificate-errors"'
