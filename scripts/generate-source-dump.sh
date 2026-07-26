#!/usr/bin/env bash
set -euo pipefail

# ── NH Core — Full Source Code Dump (Purchase Flow + Analytics) ──────────────
# Generates a txt with the complete source code of every file involved in the
# purchase funnel and/or analytics system.
#
# Usage:
#   ./scripts/generate-source-dump.sh
#   ./scripts/generate-source-dump.sh -o custom-output.txt
# ──────────────────────────────────────────────────────────────────────────────

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUTPUT="${PLUGIN_DIR}/docs/purchase-flow-source-code.txt"

while [[ $# -gt 0 ]]; do
    case "$1" in
        -o|--output) OUTPUT="$2"; shift 2 ;;
        *) echo "Unknown flag: $1"; exit 1 ;;
    esac
done

mkdir -p "$(dirname "$OUTPUT")"

# Files involved in purchase flow + analytics
FILES=(
    # Plugin core
    "nh-core.php"
    "inc/class-nh-core-loader.php"

    # Tracking system
    "inc/class-nh-core-tracking.php"

    # AJAX handlers + WooCommerce integration
    "inc/class-nh-core-woocommerce.php"
    "inc/nh-atc-helpers.php"

    # ATC templates
    "templates/nh-add-to-cart/simple.php"
    "templates/nh-add-to-cart/variable.php"
    "templates/nh-add-to-cart/external.php"

    # Cart template
    "templates/cart/cart.php"

    # Elementor widgets
    "widgets/class-nh-side-cart-widget.php"
    "widgets/class-nh-cart-widget.php"
    "widgets/class-nh-checkout-widget.php"

    # Frontend JS
    "assets/js/nh-datalayer-cart.js"
    "assets/js/nh-add-to-cart.js"
    "assets/js/nh-side-cart.js"
    "assets/js/nh-menu-cart.js"

    # CSS
    "assets/css/nh-add-to-cart.css"

    # Elementor integration
    "inc/class-nh-core-elementor.php"
    "inc/class-nh-core-icons.php"
)

{
echo "================================================================================"
echo " NH Core — Full Source Code Dump"
echo " Purchase Flow + Analytics System"
echo "================================================================================"
echo " Generated: $(date '+%Y-%m-%d %H:%M:%S %Z')"
echo " Plugin version: $(grep "NH_CORE_VERSION" "$PLUGIN_DIR/nh-core.php" | head -1 | sed "s/.*'\(.*\)'.*/\1/")"
echo "================================================================================"
echo ""

for file in "${FILES[@]}"; do
    filepath="$PLUGIN_DIR/$file"
    if [[ -f "$filepath" ]]; then
        lines=$(wc -l < "$filepath")
        echo "=========================================================================="
        echo " FILE: $file ($lines lines)"
        echo "=========================================================================="
        echo ""
        cat "$filepath"
        echo ""
        echo ""
    else
        echo "=========================================================================="
        echo " FILE: $file (NOT FOUND)"
        echo "=========================================================================="
        echo ""
    fi
done

echo "================================================================================"
echo " END OF DUMP"
echo "================================================================================"
} > "$OUTPUT"

echo "Generated: $OUTPUT ($(wc -l < "$OUTPUT") lines)"
