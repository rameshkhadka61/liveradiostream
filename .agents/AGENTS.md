# Project Rules

- **GitHub Personal Access Token:** The update checkers in `functions.php` (theme) and `mero-seo.php` (plugin) use a personal access token for authentication (`ghp_...`).
- **CRITICAL SECURITY RULE:** NEVER commit or push `functions.php` or `mero-seo.php` containing the live personal access token to any public git remote. Always revert them to placeholder comments (`// $...->setAuthentication('your-token-here');`) before running `git commit` or `git push`.
- **Version Control & Tagging:** Every time updates are pushed for either the theme (`liveradiostream`) or plugin (`mero-seo`):
  1. Bump the version number in `style.css` (theme) or `mero-seo.php` (plugin).
  2. Commit the changes (ensuring live PATs are excluded).
  3. Create a Git tag matching the new version (e.g., `vx.y.z`).
  4. Push commits and tags (`git push origin main --tags`).
