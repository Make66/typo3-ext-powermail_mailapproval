# Tournament List: Client-Side Sorting

Automatically sorts the powermail frontend list view (`Output/List`) by the start date
extracted from the **Zeitraum** column, and removes the leading row-number column.

## Files

| File | Purpose |
|------|---------|
| `Resources/Public/JavaScript/sort-tournaments.js` | Sorting logic |
| `Resources/Private/Templates/Output/List.html` | Overridden Fluid template |
| `Configuration/TypoScript/setup.typoscript` | Template path override |

## How it works

### TypoScript override

`setup.typoscript` registers the extension's template folder at priority index `10`,
which is higher than powermail's defaults (`0` = built-in, `1` = constant placeholder):

```typoscript
plugin.tx_powermail {
    view {
        templateRootPaths {
            10 = EXT:powermail_mailapproval/Resources/Private/Templates/
        }
    }
}
```

TYPO3 Fluid resolves templates from the highest index first, so
`Resources/Private/Templates/Output/List.html` in this extension shadows
the original powermail template.

### Fluid template

`Output/List.html` is identical to the powermail original with two additions:

1. **Script asset** — loads the JS via TYPO3's asset pipeline (deduplication,
   correct placement in page head/footer):

   ```html
   <f:asset.script identifier="sortTournaments"
       src="{f:uri.resource(path:'JavaScript/sort-tournaments.js',
             extensionName:'powermail_mailapproval')}" />
   ```

2. **Hook attribute** — the table receives `data-js="tournament-sort"` so the
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
