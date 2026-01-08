# Powermail Mail Approval

TYPO3 v11 Extension für die Freigabe von Powermail-Einträgen

## Features

- Backend-Modul zur Verwaltung nicht freigegebener Powermail-Einträge
- Einträge sind nach Erstellungsdatum (crdate) absteigend sortiert
- Einzelne Freigabe oder Ablehnung von Einträgen
- Automatische Filterung im Frontend (Pi2) - nur freigegebene Einträge werden angezeigt

## Installation

1. Extension in `typo3conf/ext/powermail_mailapproval/` entpacken
2. Extension im Extension Manager aktivieren
3. Datenbank-Update durchführen (neues Feld `approved` wird hinzugefügt)
4. TypoScript Template einbinden (optional, für automatische Pi2-Filterung)

## Verwendung

1. Backend-Modul "Mail Approval" im Web-Bereich öffnen
2. Liste der nicht freigegebenen Einträge wird angezeigt
3. Details anzeigen mit "Show Details"
4. Einträge freigeben mit "Approve" oder ablehnen mit "Reject"

## TypoScript

Die Extension fügt automatisch einen Filter für Pi2 hinzu:

```typoscript
plugin.tx_powermail.settings.Pi2 {
    search {
        staticPluginsVariables {
            filter {
                approved = 1
            }
        }
    }
}
```

## Technische Details

- Vendor: Taketool
- Extension Key: powermail_mailapproval
- Kompatibel mit: TYPO3 11.5
- Abhängig von: powermail ^10.0

## Lizenz

GPL-2.0-or-later
