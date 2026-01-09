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

# Configure storagePid via TYPO3 Backend

## Method 1: Direct YAML Editing (Recommended for TYPO3 v11.5)

### Edit config.yaml directly

1. Navigate to `config/sites/your-site/config.yaml`
2. Add the following:

```yaml
settings:
  powermailMailapproval:
    storagePid: 123  # Your storage page UID
```

3. Save the file (no cache clearing needed)

## Method 2: Backend Site Management (TYPO3 v12+)

For TYPO3 v12 and higher, you can edit settings via the backend:

1. Go to **Site Management > Sites**
2. Click on your site
3. Switch to **Settings** tab
4. Add custom setting

However, for **TYPO3 v11.5**, the Settings tab is limited. Use Method 1 instead.

## Method 3: Enable Settings Editing in TYPO3 v11.5

To make settings editable in the backend for TYPO3 v11.5, create a settings definition file:

### Create settings.yaml

Location: `config/sites/your-site/settings.yaml`

```yaml
settings:
  powermailMailapproval:
    storagePid:
      type: int
      label: 'Powermail Storage Page'
      description: 'Select the page where powermail form submissions are stored. Set to 0 to show all entries.'
      default: 0
```

**Note**: Full backend editing support for site settings was introduced in TYPO3 v12. For v11.5, manual YAML editing is the standard approach.

## How the Controller Gets the Page ID

The controller uses multiple fallback methods to get the current page ID:

### 1. From GET Parameters (Primary)
```php
$pageId = (int)($this->request->getQueryParams()['id'] ?? 0);
```

### 2. From POST Parameters (Fallback)
```php
$pageId = (int)($this->request->getParsedBody()['id'] ?? 0);
```

### 3. From Backend User Module Data (Last Resort)
```php
$pageId = (int)($GLOBALS['BE_USER']->getModuleData('web_PowermailMailapprovalApproval/lastPageId') ?? 0);
```

### 4. Use First Available Site (Ultimate Fallback)
```php
$sites = $this->siteFinder->getAllSites();
$site = reset($sites);
```

## Complete Example

### config.yaml
```yaml
rootPageId: 1
base: 'https://example.com/'
languages:
  - languageId: 0
    title: English
    enabled: true
    base: /

# Powermail Mail Approval Settings
settings:
  powermailMailapproval:
    storagePid: 123
```

## Multi-Site Example

### Site 1
```yaml
# config/sites/site1/config.yaml
rootPageId: 1
base: 'https://site1.com/'
settings:
  powermailMailapproval:
    storagePid: 100  # Storage for site 1
```

### Site 2
```yaml
# config/sites/site2/config.yaml
rootPageId: 50
base: 'https://site2.com/'
settings:
  powermailMailapproval:
    storagePid: 200  # Storage for site 2
```

## Verification

After configuration, verify in the backend module:
- Open the Mail Approval module
- The list should only show entries from the configured storage page
- Check the debug output: "Storage PID: 123"

## Technische Details

- Vendor: Taketool
- Extension Key: powermail_mailapproval
- Kompatibel mit: TYPO3 11.5
- Abhängig von: powermail ^10.0

## Lizenz

GPL-2.0-or-later
