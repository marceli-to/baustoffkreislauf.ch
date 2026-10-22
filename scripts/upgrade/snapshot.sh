#!/bin/bash
# usage: snapshot.sh <absolute outdir>   (URLS=<urls file> to reuse a URL list)
# Fetches every entry and term URL from Herd and normalises tokens, UUIDs and asset hashes.
set -e
cd "$(dirname "$0")/../.."
OUT=$1; rm -rf "$OUT"; mkdir -p "$OUT"
if [ -n "$URLS" ]; then cp "$URLS" "$OUT/_urls.txt"; else php -d memory_limit=1G artisan tinker --execute '
foreach (Statamic\Facades\Entry::all() as $e) { if ($e->published() && $e->url()) echo $e->absoluteUrl(), "\n"; }
foreach (Statamic\Facades\Term::all() as $t) { foreach (Statamic\Facades\Site::all() as $s) { $u = $t->in($s->handle())->absoluteUrl(); if ($u) echo $u, "\n"; } }
echo "https://baustoffkreislauf.ch.test/login\n";
' | grep '^https' | sort -u > "$OUT/_urls.txt"; fi
while read -r url; do
  f=$(echo "$url" | sed -E 's#https?://[^/]+/?##; s#/#__#g'); [ -z "$f" ] && f=_root
  code=$(curl -sk -o "$OUT/$f.html" -w "%{http_code}" "$url")
  echo "$code $url" >> "$OUT/_codes.txt"
  perl -0pi -e '
    s#<figure class="mt-20 xl:mt-40 bg-white">.*?</figure>#<!--HEADERIMG-->#s;
    s#[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}#UUID#g;
    s#(name="_token"[^>]*value=")[^"]+#$1TOKEN#g; s#(value=")[A-Za-z0-9]{40}(")#$1TOKEN$2#g;
    s#token=[A-Za-z0-9]+#token=TOKEN#g; s#<link rel="modulepreload" as="script"#<link rel="modulepreload"#g; s#(csrf-token" content=")[^"]+#$1TOKEN#g;
    s#/build/assets/([\w.-]+?)-[\w-]{8}\.(css|js)#/build/assets/$1.$2#g;
  ' "$OUT/$f.html"
done < "$OUT/_urls.txt"
wc -l < "$OUT/_urls.txt"; awk '{print $1}' "$OUT/_codes.txt" | sort | uniq -c
