#!/bin/bash
# usage: compare.sh <a> <b>   (SHOW=<lines> to print the diffs)
# Lists pages whose markup differs once whitespace is ignored.
A=$1; B=$2; mkdir -p "${TMPDIR:-/tmp}/snapshot-cmp"
for f in "$A"/*.html; do n=$(basename "$f")
  perl -0pe 's/\s+/ /g; s/> </></g; s/></>\n</g' "$f" > "${TMPDIR:-/tmp}/snapshot-cmp"/a.html
  perl -0pe 's/\s+/ /g; s/> </></g; s/></>\n</g' "$B/$n" > "${TMPDIR:-/tmp}/snapshot-cmp"/b.html
  if ! cmp -s "${TMPDIR:-/tmp}/snapshot-cmp"/a.html "${TMPDIR:-/tmp}/snapshot-cmp"/b.html; then echo "== $n"; [ -n "$SHOW" ] && diff "${TMPDIR:-/tmp}/snapshot-cmp"/a.html "${TMPDIR:-/tmp}/snapshot-cmp"/b.html | head -${SHOW} | cut -c1-220; fi
done
