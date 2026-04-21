(function () {
    'use strict';

    const MONTH_NAMES = {
        januar: 1, februar: 2, 'märz': 3, april: 4, mai: 5, juni: 6,
        juli: 7, august: 8, september: 9, oktober: 10, november: 11, dezember: 12
    };

    /**
     * Extracts the first/start date from various German date string formats:
     *   "29.08.2026"
     *   "14.08.2026 - 16.08.2026"
     *   "14.08.-16.08.2026"
     *   "14. - 16.08.2026"
     *   "21.-23.08.2026"
     *   "19.-21.6. und 27.-28.6.2026"
     *   "28. 8. 2026 - 30.8.2026"
     *   "Sonntag, 26.04.2026"
     *   "12. - 14. Juni 2026"
     *   "4. - 7 Juni 2026"
     */
    function parseFirstGermanDate(text) {
        text = text.trim();
        if (!text) return null;

        let m;

        // "DD.MM.-DD.MM.YYYY" — e.g. "14.08.-16.08.2026"
        m = text.match(/^(\d{1,2})\.(\d{1,2})\.\s*-\s*\d{1,2}\.\d{1,2}\.(\d{4})/);
        if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

        // "DD.-DD.MM.YYYY" — e.g. "14. - 16.08.2026", "21.-23.08.2026", "12.-14.06.2026"
        m = text.match(/^(\d{1,2})\.\s*-\s*\d{1,2}\.(\d{1,2})\.(\d{4})/);
        if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

        // "DD.-DD.MM. ... YYYY" — e.g. "19.-21.6. und 27.-28.6.2026" (multi-range, year at end)
        m = text.match(/^(\d{1,2})\.\s*-\s*\d{1,2}\.(\d{1,2})\./);
        if (m) {
            const yr = text.match(/(\d{4})/);
            if (yr) return new Date(+yr[1], +m[2] - 1, +m[1]);
        }

        // Standard "DD.MM.YYYY" (with optional spaces, e.g. "28. 8. 2026")
        m = text.match(/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})/);
        if (m) return new Date(+m[3], +m[2] - 1, +m[1]);

        // Month name — e.g. "12. - 14. Juni 2026", "4. - 7 Juni 2026"
        const monthRe = new RegExp(
            '(\\d{1,2})\\.[\\s\\S]*?(' + Object.keys(MONTH_NAMES).join('|') + ')\\s+(\\d{4})',
            'i'
        );
        m = text.match(monthRe);
        if (m) return new Date(+m[3], MONTH_NAMES[m[2].toLowerCase()] - 1, +m[1]);

        return null;
    }

    function sortTournamentTable(table) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        if (rows.length < 2) return;

        const headerRow = rows[0];
        const dataRows = rows.slice(1);

        // Drop first <th> (row-number column)
        const firstTh = headerRow.querySelector('th:first-child');
        if (firstTh) firstTh.remove();

        // Drop first <td> from each data row; read date from original column 2 (now column 1)
        const dated = dataRows.map(function (row) {
            const firstTd = row.querySelector('td:first-child');
            if (firstTd) firstTd.remove();

            const dateTd = row.querySelector('td:first-child');
            const dateText = dateTd ? dateTd.textContent : '';
            return { row: row, date: parseFirstGermanDate(dateText) };
        });

        // Sort ascending; rows without a parseable date go to the end
        dated.sort(function (a, b) {
            if (!a.date && !b.date) return 0;
            if (!a.date) return 1;
            if (!b.date) return -1;
            return a.date - b.date;
        });

        dated.forEach(function (item) { tbody.appendChild(item.row); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('table[data-js="tournament-sort"]').forEach(sortTournamentTable);
    });
})();
