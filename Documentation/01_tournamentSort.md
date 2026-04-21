# Tournament List: Client-Side Sorting

Automatically sorts the powermail frontend list view (`Output/List`) by the start date
extracted from the **Zeitraum** column, and removes the leading row-number column.

## Files

| File | Purpose |
|------|---------|
| `Resources/Public/JavaScript/sort-tournaments.js` | Sorting logic |
| `Resources/Private/Templates/Output/List.html` | Overridden Fluid template |
| `ext_localconf.php` | Auto-loads critical TypoScript inline |
| `Configuration/TCA/Overrides/sys_template.php` | Registers static template for backend module config |

## How it works

### TypoScript loading — why inline, not `@import`

`addTypoScript()` appends content to TYPO3's `defaultTypoScript_setup` global.
In TYPO3 11, that string is **not** run through `checkIncludeLines` (the `@import`
preprocessor), so an `@import` placed there is silently ignored.

The two critical frontend lines are therefore inlined directly in `ext_localconf.php`:

```php
ExtensionManagementUtility::addTypoScript(
    'powermail_mailapproval',
    'setup',
    'plugin.tx_powermail.view.templateRootPaths.10 = EXT:powermail_mailapproval/Resources/Private/Templates/' . LF .
    'page.includeJSFooter.sortTournaments = EXT:powermail_mailapproval/Resources/Public/JavaScript/sort-tournaments.js'
);
```

This runs unconditionally for every page request — no static template selection
required, no `@import` indirection.

### Template path override

Index `10` is higher than powermail's built-in indices (`0` = core template,
`1` = constant placeholder), so TYPO3 Fluid resolves `Output/List.html` from
this extension first.

### JS loading — why TypoScript, not `f:asset.script`

Loading the JS via `page.includeJSFooter` in TypoScript is independent of
whether the template override succeeds. `f:asset.script` inside a plugin's
Fluid template can silently fail if the AssetCollector is not wired for that
render pass. The TypoScript approach always works.

### Fluid template

`Output/List.html` is identical to the powermail original with one addition:

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
