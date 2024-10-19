#!/bin/bash

set -eu

# Create the dist directory if it doesn't exist
mkdir -p dist

# Get the current branch, tag, or commit hash
commit_info=$(git describe --tags --always)
if [ -z "$commit_info" ]; then
    commit_info=$(git rev-parse --abbrev-ref HEAD)
fi

# Get the current directory name (without the trailing slash)
current_dir=$(basename "$(pwd)")

# Create a ZIP archive of the current directory excluding .git, and save it to the dist folder with branch/tag/commit info in the filename
(cd .. && zip -r "${current_dir}/dist/${current_dir}-${commit_info}.zip" "./${current_dir}" -x "${current_dir}/.git/*" -x "${current_dir}/.*" -x "${current_dir}/dist/*" -x "${current_dir}/release.sh" -x "${current_dir}/composer.*")

echo "Archive created successfully in dist/${current_dir}-${commit_info}.zip"
