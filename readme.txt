=== Kulturhaus Events ===
Contributors: sysexperts
Tags: events, calendar, veranstaltungen, kulturhaus, barrierefreiheit
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.12.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professionelles Veranstaltungsmanagement für behördliche und kulturelle Einrichtungen. DSGVO-konform und barrierefrei (BITV 2.0 / WCAG 2.1 AA).

== Description ==

Kulturhaus Events ist ein WordPress-Plugin zur Verwaltung von Veranstaltungen, speziell entwickelt für behördliche Webseiten und Kultureinrichtungen.

**Hauptfunktionen:**

* Veranstaltungen erstellen und verwalten
* Veranstaltungsorte mit Adressdaten
* Veranstalter-Verwaltung
* Kategorien und Schlagwörter
* Listenansicht und Einzelansicht
* **Shortcodes** für flexible Event-Anzeige
* **iCal/ICS-Export** - Events zum Kalender hinzufügen
* **Filter-Widget** - Nach Datum und Kategorie filtern
* **Kommende Events Widget** - Sidebar-Integration
* DSGVO-konform (keine externen Dienste)
* Barrierefrei nach BITV 2.0 / WCAG 2.1 AA
* Vollständig auf Deutsch

**Sicherheitsfeatures:**

* Nonce-Verifizierung bei allen Formularen
* Capability-Checks für Berechtigungen
* Input Sanitization und Output Escaping
* Prepared Statements für Datenbankabfragen

== Installation ==

1. Lade das Plugin in das Verzeichnis `/wp-content/plugins/kulturhaus-events` hoch
2. Aktiviere das Plugin über das 'Plugins'-Menü in WordPress
3. Erstelle Veranstaltungen unter 'Veranstaltungen' im Admin-Bereich
4. Nutze Widgets unter Design > Widgets oder Shortcodes in Seiten/Beiträgen

== Shortcodes ==

**[kh_events]**
Zeigt eine Liste von Veranstaltungen an.

Parameter:
* limit - Anzahl der Events (Standard: 10)
* category - Kategorie-Slug
* tag - Tag-Slug
* venue - Venue-ID
* organizer - Organizer-ID
* order - ASC oder DESC (Standard: ASC)
* show_past - true/false (Standard: false)

Beispiel: `[kh_events limit="5" category="konzerte"]`

**[kh_upcoming_events]**
Zeigt kommende Veranstaltungen in kompakter Form.

Parameter:
* limit - Anzahl (Standard: 5)
* category - Kategorie-Slug

Beispiel: `[kh_upcoming_events limit="3"]`

== Widgets ==

* **Kommende Veranstaltungen** - Zeigt eine Liste kommender Events in der Sidebar
* **Veranstaltungsfilter** - Ermöglicht Filterung nach Datum und Kategorie

== Changelog ==

= 1.12.0 =
* **NEUES FEATURE:** Event-Kalender mit Monatsansicht
* Shortcode [kh_event_calendar] für Kalender-Anzeige
* Klickbare Events im Kalender
* Navigation zwischen Monaten
* Farbcodierung für Events (gelb)
* Heute-Markierung
* Responsive Design für alle Geräte
* Kategorie-Filter im Kalender möglich

= 1.11.1 =
* **BUGFIX:** Highlights CSS wird jetzt auf allen Seiten geladen (für Shortcodes)
* Grid-Layout funktioniert jetzt korrekt

= 1.11.0 =
* **NEUES FEATURE:** Featured Events - Events als Highlights markieren
* Checkbox "Als Highlight hervorheben" in Event-Details
* Highlights-Shortcode zeigt bevorzugt Featured Events, sonst kommende Events
* **DESIGN KOMPLETT NEU:** 2-Spalten-Layout wie Screenshot
* Links: Text-Block mit Titel, Untertitel, Beschreibung, Button
* Rechts: 3 Event-Karten nebeneinander mit Rotation
* Gelbe dekorative Linien oben
* Responsive Design für alle Bildschirmgrößen

= 1.10.1 =
* **NEUES FEATURE:** Shortcode-Hilfe im Admin (Veranstaltungen → Shortcodes)
* Alle Shortcodes mit Kopier-Funktion und Attribut-Übersicht
* Highlights-Design überarbeitet: Gelbe Akzente wie im Screenshot
* Schwarzer Text "HIGH-LIGHTS" mit gelbem Highlight-Effekt
* Gelber Button mit schwarzem Text
* Karten mit leichter Rotation und besseren Schatten
* Dekorative gelbe Linien

= 1.10.0 =
* **NEUES FEATURE:** Event Highlights Shortcode [kh_event_highlights]
* Modernes Grid-Layout für Featured Events auf der Startseite
* Anpassbare Überschrift, Untertitel und Button
* Responsive Design mit Hover-Effekten
* **NEUES FEATURE:** Veranstaltungen duplizieren
* "Duplizieren"-Link in der Event-Liste
* Kopiert alle Meta-Daten, Taxonomien und Featured Image
* Duplikat wird als Entwurf gespeichert

= 1.9.0 =
* **NEUES FEATURE:** WordPress Customizer Integration
* Design-Anpassungen direkt im Customizer möglich
* Farben: Akzentfarbe, Hover-Farbe, Textfarbe, Hintergrundfarbe
* Typografie: Schriftarten für Überschriften und Text, Schriftgröße
* Layout: Ecken-Rundung, Abstände
* Live-Preview im Customizer
* Alle Einstellungen unter "Design → Customizer → Kulturhaus Events Design"

= 1.8.0 =
* **VEREINFACHUNG:** Farbeinstellungen entfernt
* Feste rote Akzentfarbe (#dc143c) implementiert
* Feste dunkelrote Hover-Farbe (#b91c1c) implementiert
* Einfacheres, wartungsfreundlicheres Design-System

= 1.7.5 =
* **FINALE LÖSUNG:** Farbsystem komplett überarbeitet
* CSS-Variablen vereinheitlicht (--kh-accent-color überall)
* output_custom_css immer ausgeben, nicht nur bei Änderungen
* --kh-color-accent zusätzlich gesetzt für Kompatibilität

= 1.7.4 =
* **BUGFIX:** Akzentfarben funktionieren jetzt auch auf Homepage/Event-Listen
* CSS-Variablen in frontend.css auf anpassbare Farben umgestellt
* Alle Akzent- und Hover-Farben nutzen jetzt die Einstellungen

= 1.7.3 =
* **BUGFIX:** CSS-Variablen Fallback am Anfang der CSS-Datei hinzugefügt
* Button-Farben werden jetzt korrekt angezeigt (auch ohne Einstellungsänderung)

= 1.7.2 =
* **KRITISCHER BUGFIX:** Farbänderungen werden jetzt korrekt angewendet
* CSS-Variablen mit !important versehen für höhere Priorität
* Optimierte CSS-Struktur für bessere Überschreibbarkeit

= 1.7.1 =
* **BUGFIX:** "Undefined array key" Fehler bei Datumsformat/Zeitformat behoben
* **BUGFIX:** Akzentfarben werden jetzt korrekt gespeichert
* Farbfelder in eigene Design-Sektion verschoben

= 1.7.0 =
* **NEUES FEATURE:** Hover-Farbe jetzt separat anpassbar
* Verbesserte Admin-Einstellungsseite mit modernem Design
* Live-Farbvorschau beim Ändern der Farben
* Besseres Layout mit Header und Beschreibungen
* Erfolgsmeldung nach dem Speichern
* CSS-Variablen für Hover-Effekte implementiert

= 1.6.1 =
* **BUGFIX:** Doppelte Einstellungsseite entfernt
* Akzentfarbe jetzt in bestehende Einstellungsseite integriert
* Nur noch eine Einstellungsseite unter: Veranstaltungen → Einstellungen

= 1.6.0 =
* **NEUES FEATURE:** Einstellungsseite für Plugin-Konfiguration
* Akzentfarbe jetzt anpassbar (Standard: Gelb #ffc107)
* WordPress Color Picker für einfache Farbauswahl
* CSS-Variablen für konsistentes Design
* Einstellungen unter: Veranstaltungen → Einstellungen

= 1.5.3 =
* Preisfarbe von rot auf schwarz geändert (bessere Lesbarkeit)
* Padding unter Eintritt-Box hinzugefügt (Abstand zum Kalender-Button)
* Hinweistext jetzt unter den Preisen als einfacher Text ohne Box

= 1.5.2 =
* **KRITISCHER BUGFIX:** Event-Preise werden jetzt korrekt gespeichert
* Preise erscheinen jetzt in der Sidebar (rechts unter Ticket-Button)
* Kompaktes Sidebar-Design für Preise
* Array-Handling für Event-Preise korrigiert
* Bessere UX: Preise prominent in der rechten Spalte

= 1.5.1 - DEBUG =
* Debug-Ausgabe hinzugefügt um Preise-Problem zu identifizieren
* Prüft ob Event-Preise korrekt gespeichert werden

= 1.5.0 =
* **Preismodell-System überarbeitet** - Vorlagen definieren nur noch Struktur
* Event-spezifische Preise - jedes Event hat individuelle Preise
* Preismodell-Vorlagen: nur Labels/Kategorien (z.B. Normal, Ermäßigt, Soli)
* Event-Editor: konkrete Preise pro Kategorie eingeben
* Frontend zeigt automatisch Event-Preise statt Vorlage-Preise
* Bessere UX: Preisfelder erscheinen nach Preismodell-Auswahl

= 1.4.3 =
* Vollständiger Fix: Preismodell-System funktioniert jetzt stabil
* Autoloader lädt alle Klassen korrekt
* Alle Features aktiviert und getestet

= 1.4.2 - EMERGENCY FIX =
* KRITISCHER BUGFIX: Klassen-Existenz-Prüfung für Pricing Model Meta
* Verhindert Fatal Error beim Plugin-Laden
* WordPress Admin sollte wieder erreichbar sein

= 1.4.1 =
* Bugfix: KH_Pricing_Model_Meta Klasse fehlte - jetzt erstellt
* Preismodell-Editor funktioniert jetzt vollständig

= 1.4.0 =
* **Preismodell-System aktiviert** - Wiederverwendbare Preismodell-Vorlagen
* Preismodell-Dropdown im Event-Editor
* Saubere Integration in neue Einzelseite
* 4 Preismodell-Typen: Kostenlos, Solidarisch, Fest, Gestaffelt
* Moderne Preisdarstellung mit Karten und Hover-Effekten
* Slider-Bugfix: overflow hidden für korrekte Funktion
* Gelbe Akzente für Preismodell-Hinweise

= 1.3.3 =
* Bugfix: Gallery-Feld fehlte in get_fields() - Admin-Seite funktioniert wieder

= 1.3.2 =
* **Bildergalerie-Feature** - Mehrere Bilder für Slider hochladen
* Titelbild jetzt boxed (max-width 1200px) mit runden Ecken
* Funktionierender Slider mit Pfeilen und Dots
* Touch/Swipe-Support für mobile Geräte
* Keyboard-Navigation (Pfeiltasten)
* Gallery-Meta-Box im Event-Editor

= 1.3.1 =
* Template und CSS komplett neu geschrieben
* Sauberer, optimierter Code
* Exakt nach Screenshot-Design

= 1.3.0 =
* **Neues Design** - Event-Einzelseite komplett nach Screenshot-Design umgebaut
* Bild-Slider mit gelben Navigationspfeilen
* 2-Spalten-Layout: Content links, Sidebar rechts
* Gelbe Highlight-Farbe für Icons und Buttons
* Moderne Meta-Box mit Icons (Datum, Ort, Preis)
* Gelber "TICKETS BEI RESERVIX" Button
* Responsive Design optimiert

= 1.2.2 =
* Bugfix: save_meta Methode vollständig implementiert (kein Alias mehr)
* Plugin muss komplett deinstalliert und neu installiert werden

= 1.2.1 =
* Bugfix: save_meta Methode in KH_Event_Meta hinzugefügt
* Event-Erstellung funktioniert jetzt

= 1.2.0 =
* **Flexibles Preismodell-System** - Erstellen Sie wiederverwendbare Preismodell-Vorlagen
* **Kostenlose Veranstaltungen** - Mit optionalem Hinweistext
* **Solidarisches Preissystem** - Mit 3 Preisstufen und ausführlichem Hinweistext
* **Feste & Gestaffelte Preise** - Für Standard-Ticketpreise
* **Preismodell-Verwaltung** - Eigener Bereich unter "Veranstaltungen > Preismodelle"
* **Moderne Preisdarstellung** - Übersichtliche Karten mit Hover-Effekten
* Datum & Uhrzeit jetzt prominent im Hero-Bereich (bei Featured Image)
* Verbesserte Event-Template-Struktur

= 1.0.0 =
* Erstveröffentlichung
* Custom Post Types: Events, Venues, Organizer
* Taxonomien: Kategorien, Schlagwörter
* Meta-Boxen für Veranstaltungsdetails
* Einzelansicht und Archivansicht
* Admin-Spalten mit Sortierung
* BITV 2.0 / WCAG 2.1 AA konformes Frontend
* Shortcodes: [kh_events] und [kh_upcoming_events]
* iCal/ICS-Export für einzelne Veranstaltungen
* Filter-Widget für Datum und Kategorien
* Kommende Events Widget für Sidebar
* Query-Filter für Event-Archive
