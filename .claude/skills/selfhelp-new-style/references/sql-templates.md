# SQL templates

## Core — new script `server/db/update_scripts/NN_update_vA.B.C_vX.Y.Z.sql`

When appending to an unreleased script instead, add only the style block before its `COMMIT;`.

```sql
START TRANSACTION;

UPDATE version
SET version = 'vX.Y.Z';

-- ---------------------------------------------------------------------------
-- Style: progressCard
-- <what it does, any non-obvious decision>
-- ---------------------------------------------------------------------------

INSERT IGNORE INTO `styles` (`name`, `id_type`, `id_group`, `description`)
VALUES (
    'progressCard',
    (SELECT id FROM styleType WHERE name = 'view'),       -- or 'component'
    (SELECT id FROM styleGroup WHERE name = 'Wrapper'),
    'A card showing a title and a progress bar. Children are rendered below the bar.'
);

-- New fields only (reuse existing ones; grep first)
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`)
VALUES (NULL, 'progress_value', get_field_type_id('number'), 0);

-- Style-specific links
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('title'), NULL, 'Heading shown on top of the card.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('progress_value'), '0', 'Progress in percent (0-100). Supports `{{...}}` interpolation from data_config.');

-- Standard links (every style)
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('css'), NULL, 'Allows to assign CSS classes to the root item of the style.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('css_mobile'), NULL, 'Allows to assign CSS classes to the root item of the style for the mobile version.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('condition'), NULL, 'The field `condition` allows to specify a condition. Only if the condition resolves to true the style is rendered.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('data_config'), NULL, 'Define data_config to load data that can be used in the style fields via `{{...}}`.');

INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('debug'), 0, 'If *checked*, debug messages will be rendered to the screen. These might help to understand the result of a condition evaluation. **Make sure that this field is *unchecked* once the page is productive**.');

-- Containers only
INSERT IGNORE INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`)
VALUES (get_style_id('progressCard'), get_field_id('children'), 0, 'Child sections rendered inside the card.');

COMMIT;

```
(keep the trailing empty line)

## Plugin — `server/plugins/<plugin>/server/db/vX.Y.Z.sql`

Same style/fields/styles_fields blocks, wrapped like this:

```sql
-- =====================================================
-- <Plugin> plugin vX.Y.Z - <short summary>
-- Requires vA.B.C. Clear the SelfHelp cache after running (styles/fields are cached).
-- =====================================================

START TRANSACTION;

-- ... styles / fields / styles_fields ...

-- Bump the plugin version LAST so a failed statement never marks this version installed.
UPDATE plugins SET version = 'vX.Y.Z' WHERE `name` = '<plugin>';

COMMIT;
```

The very first plugin migration (`v1.0.0.sql`) registers the plugin instead:
`INSERT IGNORE INTO plugins (`name`, version) VALUES ('<plugin>', 'v1.0.0');`

## Adding a new field type (rare)

```sql
INSERT IGNORE INTO `fieldType` (`id`, `name`, `position`) VALUES (NULL, 'select-my-thing', '8');
```
Only when the CMS needs a new editor widget; it also needs CMS-side rendering support.
