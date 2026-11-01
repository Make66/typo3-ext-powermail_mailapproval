# Tournament List: Client-Side Sorting

Automatically sorts the powermail frontend list view (`Output/List`) by the start date
extracted from the **Zeitraum** column, and removes the leading row-number column.

## Files

| File | Purpose |
|------|---------|
| `Resources/Public/JavaScript/sort-tournaments.js` | Sorting logic |
| `Resources/Private/Templates/Output/List.html` | Overridden Fluid template |
| `ext_localconf.php` | Auto-loads the template path override inline |

## How it works

### TypoScript loading — why inline, not `@import`

`addTypoScript()` appends content to TYPO3's `defaultTypoScript_setup` global.
In TYPO3 11, that string is **not** run through `checkIncludeLines` (the `@import`
preprocessor), so an `@import` placed there is silently ignored.

The template path override is therefore inlined directly in `ext_localconf.php`:

```php
ExtensionManagementUtility::addTypoScript(
    'powermail_mailapproval',
    'setup',
    'plugin.tx_powermail.view.templateRootPaths.10 = EXT:powermail_mailapproval/Resources/Private/Templates/' . LF
);
```

This runs unconditionally for every page request — no static template selection
required, no `@import` indirection.

### Template path override

Index `10` is higher than powermail's built-in indices (`0` = core template,
`1` = constant placeholder), so TYPO3 Fluid resolves `Output/List.html` from
this extension first.

### JS loading

The script is loaded with `f:asset.script` at the top of `Output/List.html`, so
it is only included on pages that render the list.

### Fluid template

`Output/List.html` is the powermail 12 original with two additions:

- **Script tag** — `f:asset.script` loads `sort-tournaments.js`.
- **Hook attribute** — the table receives `data-js="tournament-sort"` so the
   script can find it without relying on fragile CSS class selectors.

### JavaScript module (`sort-tournaments.js`)

Runs as an IIFE on `DOMContentLoaded`. Entry point:

```js
document.querySelectorAll('table[data-js="tournament-sort"]')
    .forEach(sortTournamentTable);
```

**`sortTournamentTable(table)`**

1. Splits `<tbody>` rows into header row (first) and data rows (rest).
2. Removes the first `<th>` (row-number heading).
3. For each data row:
   - Removes the first `<td>` (row-number cell).
   - Reads `textContent` of the new first cell (Zeitraum).
   - Calls `parseFirstGermanDate()` to get a sortable `Date`.
4. Sorts the rows ascending; rows without a parseable date go to the end.
5. Re-appends sorted rows to `<tbody>`.

**`parseFirstGermanDate(text)`**

Tries patterns in order of specificity. Returns a `Date` or `null`.

| Pattern | Example input | Extracted start date |
|---------|--------------|---------------------|
| `DD.MM.-DD.MM.YYYY` | `14.08.-16.08.2026` | 14.08.2026 |
| `DD.-DD.MM.YYYY` | `14. - 16.08.2026`, `21.-23.08.2026` | 14.08.2026 / 21.08.2026 |
| `DD.-DD.MM. … YYYY` | `19.-21.6. und 27.-28.6.2026` | 19.06.2026 |
| `DD.MM.YYYY` (with optional spaces) | `29.08.2026`, `28. 8. 2026` | as written |
| `DD.MM.YYYY - DD.MM.YYYY` | `14.08.2026 - 16.08.2026` | 14.08.2026 |
| `Weekday, DD.MM.YYYY` | `Sonntag, 26.04.2026` | 26.04.2026 |
| `DD. … MonthName YYYY` | `12. - 14. Juni 2026`, `4. - 7 Juni 2026` | 12.06.2026 / 04.06.2026 |

Recognised German month names: `Januar` – `Dezember` (case-insensitive, including `März`).

## Behaviour notes

- The first `<tr>` inside `<tbody>` is treated as the header row because powermail
  renders `<th>` cells inside `<tbody>` (not `<thead>`). The script matches this
  structure directly rather than querying `<thead>`.
- Sorting is purely client-side; the server still delivers rows in powermail's
  default order.
- The script is scoped to `[data-js="tournament-sort"]` tables only, so it does
  not affect other powermail list views on the same page.

## TYPO3 12 / PHP 8.1 review (2026-11-01)

Review of the whole extension after the port to TYPO3 12.4 / powermail 12
(commit `ac3f6d5`), and the fixes that followed. All PHP files lint clean on
PHP 8.1.34.

### Fixed

1. **Frontend list showed unapproved entries.** The original filter lived in
   `MyMailRepository`; its DI alias was removed in `8690b13` (so already broken on
   v11) and the class was deleted in the port. The `PM_ListViewExtended` hook and
   the `staticPluginsVariables.filter.approved` TypoScript never worked with
   powermail 12 and have been removed.

   Now: `Classes/Domain/Repository/MailRepository.php` extends powermail's
   repository and is aliased to it in `Configuration/Services.yaml` (powermail's
   controllers get the repository through constructor DI, so an XCLASS would not
   apply). It overrides only `findListBySettings()`: it takes the query of the
   parent result and adds `approved = 1` to its constraint. This covers the list
   and the export in `OutputController`. The class name matters: Extbase derives
   the model from it, so it maps to our `Domain\Model\Mail`, which has the
   `approved` property.

2. **Event listener was never registered** (`#[AsEventListener]` is TYPO3 13 only)
   and would not have persisted anything. Removed; the DB default `approved = 0`
   already makes new entries pending.

3. **Edit/Delete links in the frontend list.** powermail 12 moved edit/delete into
   the Pi4 plugin; `Output/List.html` now uses `pluginName="Pi4"` like the core
   template.

### Deprecations removed

- `FlashMessage::*` → `ContextualFeedbackSeverity::*`.
- `\PDO::PARAM_INT` → `Connection::PARAM_INT`.
- Backend module renders through `ModuleTemplate::assignMultiple()` +
  `renderResponse()`; `Approval/List.html` and `Approval/Show.html` use the backend
  `Module` layout (`<f:section name="Content">`). The template-path setup in
  `initializeAction()` is gone (templates resolve via the module's package).
- TCA checkbox items use `['label' => '']`.

### Site upgrade note

The tournament list page returned HTTP 500 (`No Content Object definition found at
TypoScript object path "tt_content.list.20.powermail_pi2"`) until the powermail
wizard `powermailPluginUpdater` ran. powermail 12 registers its plugins as content
elements (`CType = powermail_pi2`). `scripts/upgrade-v12.sh` runs all wizards, so
production is covered; local DBs imported without it need
`typo3 upgrade:run powermailPluginUpdater`.

### Verified (DDEV, 2026-11-01)

- Frontend `/turniere-anzeigen` (hvrhp): with all entries pending the list is
  empty; approving two entries shows exactly those two.
- Backend module: list with pagination, details, approve (single flash message,
  returns to details) and reject all work in the Module layout.
- Not tested: a real form submission from the frontend.

### Still open

- `.idea/` is committed.
- `Configuration/TypoScript/*.typoscript` (`module.tx_powermailmailapproval`) is
  not loaded anywhere and not read by the controller.
