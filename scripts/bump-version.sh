#!/usr/bin/env bash
set -euo pipefail

# ── NH Core — Version bumper ────────────────────────────────────────────────
# Updates version in all PHP files and creates a git tag.
#
# Usage:
#   ./scripts/bump-version.sh 1.3.0
#   ./scripts/bump-version.sh 1.3.0 --push    # also pushes tag to origin
#   ./scripts/bump-version.sh 1.3.0 --release  # pushes + creates GitHub release
# ──────────────────────────────────────────────────────────────────────────────

if [[ $# -lt 1 ]]; then
    echo "Usage: $0 <version> [--push] [--release]"
    echo "Example: $0 1.3.0"
    exit 1
fi

VERSION="$1"
PUSH=false
RELEASE=false

for arg in "${@:2}"; do
    case "$arg" in
        --push)   PUSH=true ;;
        --release) RELEASE=true ;;
        *) echo "Unknown flag: $arg"; exit 1 ;;
    esac
done

# Validate semver
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: Version must be semver (e.g., 1.3.0)"
    exit 1
fi

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
TAG="v$VERSION"

echo "Bumping NH Core to $VERSION"

# ── 1. Update files ────────────────────────────────────────────────────────

# nh-core.php header comment: * Version: X.Y.Z
sed -i "s/^\( \* Version:\) .*/\1 $VERSION/" "$PLUGIN_DIR/nh-core.php"

# nh-core.php constant: define( 'NH_CORE_VERSION', 'X.Y.Z' )
sed -i "s/define( 'NH_CORE_VERSION', '[^']*'/define( 'NH_CORE_VERSION', '$VERSION'/" "$PLUGIN_DIR/nh-core.php"

# class-nh-core-tracking.php header: @version X.Y.Z
sed -i "s/^\( \* @version\) .*/\1 $VERSION/" "$PLUGIN_DIR/inc/class-nh-core-tracking.php"

# class-nh-core-tracking.php enqueue: 'X.Y.Z', (wp_enqueue_script version param)
sed -i "/wp_enqueue_script/,/nh-datalayer-cart/{s/'[0-9]*\.[0-9]*\.[0-9]*',/'$VERSION',/}" "$PLUGIN_DIR/inc/class-nh-core-tracking.php"

echo "  Files updated"

# ── 2. Verify ──────────────────────────────────────────────────────────────

echo ""
echo "Verifying..."
grep "Version: $VERSION" "$PLUGIN_DIR/nh-core.php" > /dev/null && echo "  ✓ nh-core.php header"
grep "NH_CORE_VERSION', '$VERSION'" "$PLUGIN_DIR/nh-core.php" > /dev/null && echo "  ✓ nh-core.php constant"
grep "@version $VERSION" "$PLUGIN_DIR/inc/class-nh-core-tracking.php" > /dev/null && echo "  ✓ tracking.php header"
grep "'$VERSION'," "$PLUGIN_DIR/inc/class-nh-core-tracking.php" > /dev/null && echo "  ✓ tracking.php enqueue version"

# ── 3. Git commit + tag ────────────────────────────────────────────────────

cd "$PLUGIN_DIR"

# Check if tag already exists
if git tag -l | grep -qx "$TAG"; then
    echo ""
    echo "Error: Tag $TAG already exists."
    echo "Delete it first with: git tag -d $TAG && git push origin :refs/tags/$TAG"
    exit 1
fi

git add nh-core.php inc/class-nh-core-tracking.php
if git diff --cached --quiet; then
    echo ""
    echo "  ⚠ No changes to commit (version already $VERSION)"
else
    git commit -m "release: $TAG"
fi

git tag -a "$TAG" -m "NH Core $VERSION"
echo ""
echo "  ✓ Created tag $TAG"

# ── 4. Push (optional) ─────────────────────────────────────────────────────

if $PUSH || $RELEASE; then
    git push origin main
    git push origin "$TAG"
    echo "  ✓ Pushed commit + tag"
fi

# ── 5. GitHub release (optional) ────────────────────────────────────────────

if $RELEASE; then
    if command -v gh &> /dev/null; then
        gh release create "$TAG" \
            --title "NH Core $VERSION" \
            --generate-notes
        echo "  ✓ GitHub release created"
    else
        echo "  ⚠ gh CLI not found — skipping GitHub release"
    fi
fi

echo ""
echo "Done: NH Core $VERSION ($TAG)"
