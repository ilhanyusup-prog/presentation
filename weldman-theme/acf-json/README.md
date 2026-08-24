# ACF Local JSON

This directory is wired up in `functions.php` via the `acf/settings/save_json`
and `acf/settings/load_json` filters.

The theme's field groups are primarily registered in code
(`inc/acf-fields.php`, via `acf_add_local_field_group()`), so they are
already fully version-controlled and identical across every environment
without any manual export step.

If an editor ever adjusts a field group from **wp-admin → Custom Fields**
(for example to add a one-off field), ACF will automatically write the
updated group to a `group_*.json` file in this folder. Commit that file so
the change ships with the theme on the next deploy — `acf_add_local_field_group()`
calls for the same `key` will simply be overridden by the newer local JSON
version, so there is no conflict between the two mechanisms.
