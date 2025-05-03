#!/bin/bash

# Script to remove comments and empty lines from code files
# Created on: $(date)

echo "Starting code cleanup process..."

# Find all relevant code files
find_command="find app public sql \( -name '*.php' -o -name '*.js' -o -name '*.css' -o -name '*.sql' -o -name '*.html' \)"

# Step 1: Remove comments - only those with space after //
echo "Removing comments..."
eval "$find_command -print0" | while IFS= read -r -d '' file; do
  echo "Processing: $file"
  perl -i -0777 -pe '
    s{/\*[^*]*\*+(?:[^/*][^*]*\*+)*/}{}gs;  # C-style block comments
    s{(^|[^:])//\s+.*}{$1}gm;               # // line comments with at least one space after //
    s{--[ \t].*$}{}gm;                      # SQL -- comments
    s{<!--.*?-->}{}gs;                      # HTML/XML comments
  ' "$file"
done

# Step 2: Remove empty lines
echo "Removing empty lines..."
eval "$find_command -print0" | while IFS= read -r -d '' file; do
  perl -i -ne 'print unless /^\s*$/' "$file"
done

echo "Code cleanup complete!"
