#!/usr/bin/env bash
#
# Rebuild the static site from the theme source.
#
#   ./build.sh
#
# The WordPress theme in theme/ is rendered to flat HTML by
# tools/static-export.php, which stands in for WordPress: it provides the core
# functions the templates call, fills the site with the theme's own demo
# content, and rewrites every permalink to a .html file next to the others, so
# the result runs from a project subfolder with no server behind it.
#
# The video clips are not duplicated in theme/ — they live once, in the
# published tree, and are copied back in before rendering.

set -euo pipefail
cd "$(dirname "$0")"

THEME_OUT=".tmp-build/wp-content/themes/lemon-mint-films"
PLUG_OUT=".tmp-build/wp-content/plugins/lmf-preloader"

rm -rf .tmp-build
mkdir -p "$THEME_OUT" "$PLUG_OUT"

cp -r theme/assets "$THEME_OUT/assets"
cp theme/style.css "$THEME_OUT/style.css"
cp -r theme/inc/preloader/assets "$PLUG_OUT/assets"

# the clips
mkdir -p "$THEME_OUT/assets/video"
if [ -d "wp-content/themes/lemon-mint-films/assets/video" ]; then
  cp -r wp-content/themes/lemon-mint-films/assets/video/. "$THEME_OUT/assets/video/"
fi

php tools/static-export.php "$(pwd)/.tmp-build"

rm -f ./*.html
rm -rf wp-content
mv .tmp-build/*.html .
mv .tmp-build/wp-content .
rm -rf .tmp-build

echo "Built. Open index.html, or commit and push for GitHub Pages."
