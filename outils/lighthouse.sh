#!/usr/bin/env bash
# Itération Y · Audit Lighthouse de la fiche du produit star (mobile + ordinateur), rapports dans docs/lighthouse/.
# Usage : bash outils/lighthouse.sh   (depuis le dossier du thème ; nécessite Node.js et Google Chrome)
set -euo pipefail
THEME="$(cd "$(dirname "$0")/.." && pwd)"
SITE="$(cd "$THEME/../../.." && pwd)"
command -v npx >/dev/null || { echo "  Node.js absent : audit à faire dans Chrome › DevTools › Lighthouse (voir docs/TESTS-ITY.md)"; exit 0; }
URL="$(cd "$SITE" && ddev wp eval 'echo get_permalink( gongcha_produit_star() );')"
mkdir -p "$THEME/docs/lighthouse"
echo "  Page auditée : $URL"
for mode in mobile desktop; do
	extra=(); [ "$mode" = desktop ] && extra=(--preset=desktop)
	npx -y lighthouse@12 "$URL" --quiet ${extra[@]+"${extra[@]}"} \
		--only-categories=performance,accessibility,best-practices,seo \
		--chrome-flags="--headless=new --ignore-certificate-errors" \
		--output=html --output=json --output-path="$THEME/docs/lighthouse/produit-$mode" || { echo "  Lighthouse a échoué ($mode)"; continue; }
	node -e '
		const r = require(process.argv[1]); const c = r.categories;
		const s = k => Math.round(c[k].score * 100);
		const ligne = ["performance","accessibility","best-practices","seo"].map(k => `${c[k].title} ${s(k)}`).join(" · ");
		const bas = Object.keys(c).filter(k => s(k) < 80);
		console.log(`  ${process.argv[2].padEnd(8)} ${ligne}${bas.length ? "   ⚠ sous 80 : " + bas.join(", ") : "   ✓"}`);
	' "$THEME/docs/lighthouse/produit-$mode.report.json" "$mode"
done
