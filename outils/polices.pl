#!/usr/bin/perl
# Itération Y · Télécharge les polices Google (sous-ensemble latin, suffisant pour le français)
# dans assets/fonts/ et écrit assets/css/polices.css avec des chemins locaux.
# Usage : perl outils/polices.pl <dossier-du-theme>
use strict; use warnings;
my $theme = shift or die "dossier du thème manquant\n";
my $url = 'https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;600;700&family=Vidaloka&display=swap';
my $ua  = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';
my $css = `curl -fsSL -A '$ua' '$url'`;
die "téléchargement de la feuille Google impossible\n" if $? or $css !~ /\@font-face/;
mkdir "$theme/assets/fonts";
my (%fichiers, $sortie);
$sortie = "/* Itération Y · Polices hébergées localement (générées par outils/polices.pl, sous-ensemble latin). */\n";
while ( $css =~ m{/\*\s*([\w-]+)\s*\*/\s*(\@font-face\s*\{.*?\})}gs ) {
	my ( $sous, $bloc ) = ( $1, $2 );
	next unless $sous eq 'latin';
	my ($famille) = $bloc =~ /font-family:\s*'([^']+)'/;
	my ($source)  = $bloc =~ /url\(([^)]+\.woff2)\)/ or next;
	unless ( $fichiers{$source} ) {
		( my $nom = lc $famille ) =~ s/\s+/-/g;
		my $n = 1 + grep { /^\Q$nom\E/ } values %fichiers;
		$fichiers{$source} = $n > 1 ? "$nom-$n.woff2" : "$nom.woff2";
		system( 'curl', '-fsSL', '-o', "$theme/assets/fonts/$fichiers{$source}", $source ) == 0
			or die "téléchargement de $source impossible\n";
	}
	$bloc =~ s/url\([^)]+\)/url(..\/fonts\/$fichiers{$source})/;
	$sortie .= "$bloc\n";
}
die "aucune police latine trouvée\n" unless %fichiers;
open my $fh, '>', "$theme/assets/css/polices.css" or die $!;
print $fh $sortie;
close $fh;
print "  ", scalar( keys %fichiers ), " fichier(s) : ", join( ', ', sort values %fichiers ), "\n";
