=== Kulturhaus Events ===
Contributors: sysexperts
Tags: events, calendar, veranstaltungen, kulturhaus, barrierefreiheit
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.22.3
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
* **CSV Import/Export** - Events importieren und exportieren
* **Filter-Widget** - Nach Datum und Kategorie filtern
* **Kommende Events Widget** - Sidebar-Integration
* DSGVO-konform (keine externen Dienste)
* Barrierefrei nach BITV 2.0 / WCAG 2.1 AA
* Vollständig auf Deutsch bei allen Formularen
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

Beispiel: `[kh_events limit="10" category="konzerte"]`

**[kh_upcoming_events]**
Zeigt kommende Veranstaltungen an.

Parameter:
* limit - Anzahl (Standard: 5)
* category - Kategorie-Slug

Beispiel: `[kh_upcoming_events limit="3"]`

**[kh_event_program]**
Zeigt Event-Programm im 2-Spalten Layout (60% Events, 40% News)

Parameter:
* limit - Anzahl Events (Standard: 5)
* month - Monat (1-12, optional für monatliche Ansicht)
* year - Jahr (optional für monatliche Ansicht)
* category - Kategorie-Slug
* show_navigation - true/false (Standard: false)
* show_all_link - true/false (Standard: true)
* all_link_url - URL für "Alle Veranstaltungen" Link (Standard: /veranstaltungen/)

Beispiele:
`[kh_event_program]` - Zeigt die nächsten 5 Events mit News-Bereich
`[kh_event_program limit="10"]` - Zeigt die nächsten 10 Events
`[kh_event_program show_all_link="false"]` - Ohne "Alle Veranstaltungen" Link

**[kh_event_calendar]**
Zeigt einen Monatskalender mit Events an

Parameter:
* month - Monat (1-12, optional, Standard: aktueller Monat)
* year - Jahr (optional, Standard: aktuelles Jahr)
* category - Kategorie-Slug

Beispiele:
`[kh_event_calendar]` - Zeigt aktuellen Monat
`[kh_event_calendar month="12" year="2024"]` - Zeigt Dezember 2024
`[kh_event_calendar category="konzerte"]` - Nur Konzerte

Navigation: Über Pfeile zwischen Monaten wechseln
Events: Klickbare Event-Badges in jedem Tag
Heute: Rot markiert

**[kh_event_search]**
Zeigt Suchfeld und Filter für Events an

Parameter:
* show_filters - true/false (Standard: true)
* results_layout - grid/list (Standard: grid)

Beispiele:
`[kh_event_search]` - Volle Suche mit allen Filtern
`[kh_event_search show_filters="false"]` - Nur Suchfeld

Features:
* AJAX Live-Suche (ohne Reload)
* Filter: Kategorie, Monat, Ort
* "Filter zurücksetzen" Button
* Ergebnisse im Grid-Layout (3 Spalten)
* Responsive Design

== Widgets ==

* **Kommende Veranstaltungen** - Zeigt eine Liste kommender Events in der Sidebar
* **Veranstaltungsfilter** - Ermöglicht Filterung nach Datum und Kategorie

== Changelog ==

= 1.22.3 =
* **DESIGN:** Filterleiste von `[kh_event_program]` überarbeitet (schlankere Inputs/Dropdowns)
* **DESIGN:** Typografie und Abstände in Suchfeld/Selects feiner auf Karten-Design abgestimmt
* **DESIGN:** Reset-Button deutlich dezenter und kleiner gestaltet
* **DESIGN:** Mobile-Layout der Filteraktionen verbessert (Reset + Counter kompakter)

= 1.22.2 =
* **ENTFERNT:** Backup & Restore final vollständig aus dem Plugin entfernt
* Entfernte Klassen: `class-backup-page.php`, `class-backup-manager.php`, `class-restore-manager.php`
* **BUGFIX:** CSV-Import setzt Featured Images wieder zuverlässig (auch bei URLs ohne Dateiendung, z. B. Unsplash)
* Verbesserte Bildverarbeitung im Import über Download + `media_handle_sideload()`

= 1.22.1 =
* **BUGFIX:** Suchfeld im `[kh_event_program]` Filter funktioniert wieder zuverlässig
* **BUGFIX:** AJAX-Filter nutzt jetzt den korrekten Post Type (`kh_event`)
* **BUGFIX:** Verbesserte Initialisierung bei mehreren `[kh_event_program]` Instanzen auf einer Seite
* **DESIGN:** Filterleiste visuell an Kartenstil des Program-Widgets angeglichen (Accent-Bar, Border, Input-States)

= 1.22.0 =
* **ENTFERNT:** Backup-Funktion komplett entfernt
* CSV Import/Export-Funktion bleibt erhalten
* Plugin-Code vereinfacht und stabilisiert
* Fokus auf Kern-Funktionalität

= 1.21.6 =
* **BUGFIX:** admin-post.php Handler korrekt implementiert
* Backup-Formular nutzt jetzt WordPress admin_post Action
* Redirect nach Backup-Erstellung zur Backup-Seite
* Erfolgsmeldungen via GET-Parameter
* Korrekte Nonce-Prüfung und Berechtigungsprüfung

= 1.21.5 =
* **BUGFIX:** Backup-Formular aus Einstellungs-Formular herausgenommen
* Verschachtelte Formulare verhinderten korrekte Verarbeitung
* Explizite action-URL zum Backup-Endpunkt
* Separates Formular mit eigenem Nonce
* admin-post.php Weiterleitung endgültig behoben

= 1.21.4 =
* **BUGFIX:** Backup-Button komplett überarbeitet - AJAX entfernt
* Einfache POST-Formular-Lösung implementiert
* Backup-Erstellung ohne JavaScript/AJAX
* Direkte Server-Verarbeitung mit Reload
* Stabile und zuverlässige Lösung

= 1.21.3 =
* **BUGFIX:** Backup-Button weiße Seite (admin-post.php) behoben
* AJAX-URL korrigiert mit Fallback zu admin-ajax.php
* Bessere Fehlerbehandlung mit spezifischen Fehlermeldungen
* Test-Handler für AJAX-Endpunkt hinzugefügt
* Exception-Handling im Backup-Prozess
* Console-Logs für detailliertes Debugging

= 1.21.2 =
* **BUGFIX:** Backup-Button weiße Seite behoben
* JavaScript mit verbessertem Debugging und Error Handling
* AJAX-Handler mit Logging für Fehlersuche
* Bessere Fehlermeldungen im Frontend
* Console-Logs für Debug-Zwecke hinzugefügt

= 1.21.1 =
* **BUGFIX:** Fatal Error bei Plugin-Aktivierung behoben
* Backup-Klassen werden jetzt korrekt geladen
* Manuelles Laden der Backup-Klassen vor der Initialisierung

= 1.21.0 =
* **NEUES FEATURE:** Vollständiges Backup-System implementiert
* Backup-Einstellungen im Admin-Menü (Veranstaltungen → Backup)
* Automatische Backups (täglich, wöchentlich, monatlich)
* Manuelle Backup-Erstellung mit einem Klick
* Backup-Typen: Events, Einstellungen, Voll-Backup
* Medien-Dateien optional in Backups einbeziehen
* Flexible Speicherorte (wp-content oder benutzerdefiniert)
* Alte Backups automatisch aufräumen
* E-Mail-Benachrichtigungen bei Backup-Erstellung
* JSON-Format für einfache Wiederherstellung
* **BUGFIX:** class-activator.php Fehler behoben
* Plugin komplett überarbeitet und stabilisiert

= 1.20.4 =
* **BUGFIX:** AJAX-Handler Konflikt behoben - filter_program aus KH_Ajax entfernt
* filter_program wird nur noch in KH_Ajax_Program_Filter registriert

= 1.20.3 =
* **DESIGN:** Filter-Leiste an Widget-Design angepasst
* Dezenterer Reset-Button (grau, nur Hintergrund bei Hover)
* Kleinere Labels und Icons
* Dünnere Borders (1px statt 2px)
* Passende Border-Radius (16px wie Event-Items)
* Gleiche Box-Shadow wie Event-Items
* **BUGFIX:** Program JS wird jetzt korrekt geladen
* **BUGFIX:** AJAX-Daten für Filter werden bereitgestellt

= 1.20.2 =
* **NEU:** Filter-Leiste für `[kh_event_program]` Widget
* Suchfeld für Event-Namen (mit Debouncing)
* Kategorie-Filter Dropdown
* Zeitraum-Filter (Heute, Diese Woche, Dieser Monat, Nächster Monat, Dieses Jahr, Kommende Events)
* "Zurücksetzen" Button
* Live Ergebnis-Counter
* AJAX-basierte Filterung ohne Reload
* Top Bar Layout (horizontal über dem Widget)
* Modernes Design mit roter Akzentfarbe (#dc143c)
* Responsive Design (Desktop, Tablet, Mobile)
* Loading-Animation während Filterung
* Visual Feedback für aktive Filter

= 1.20.1 =
* **BUGFIX:** TypeError in event_search Shortcode behoben (mktime() Parameter müssen int sein)
* Explizites Type-Casting für $month und $year in Monats-Dropdown

= 1.20.0 =
* **NEU:** Event-Kalender Ansicht mit Shortcode `[kh_event_calendar]`
* Monatskalender mit Navigation (Pfeile für vor/zurück)
* Events als rote Badges in Kalendertagen
* Heutiger Tag rot markiert
* Klickbare Events führen zur Detailseite
* Rote Akzentfarbe (#dc143c) statt gelb
* Filter nach Kategorie möglich
* Neue CSS-Datei: calendar.css

= 1.19.0 =
* **NEU:** Event-Kalender Ansicht mit Shortcode `[kh_event_calendar]`
* Monatskalender mit Navigation (Pfeile für vor/zurück)
* Events als rote Badges in Kalendertagen
* Heutiger Tag rot markiert
* Klickbare Events führen zur Detailseite
* Responsive Design (Desktop, Tablet, Mobile)
* Rote Akzentfarbe (#dc143c) statt gelb
* Filter nach Kategorie möglich
* Neue CSS-Datei: calendar.css

= 1.18.3 =
* **ÄNDERUNG:** Sidebar scrollt jetzt mit (nicht mehr sticky)
* **BUGFIX:** Veranstalter wird jetzt angezeigt (Status-Prüfung entfernt)
* Ort und Veranstalter werden auch bei Status "Entwurf" angezeigt

= 1.18.2 =
* **BUGFIX:** Buttons jetzt volle Breite (100%) und gleich groß
* **BUGFIX:** Ort und Veranstalter Anzeige-Logik verbessert
* Venue und Organizer müssen "veröffentlicht" sein um angezeigt zu werden
* Besseres Padding für Buttons (14px statt 10px)

= 1.18.1 =
* **BUGFIX:** Tickets-Button Hintergrundfarbe (rot) hinzugefügt
* **BUGFIX:** Ort wird jetzt korrekt angezeigt (Venue-Name + Stadt)
* **BUGFIX:** Veranstalter wird jetzt in Sidebar angezeigt (mit Icon)
* Bessere Informationsdarstellung auf Einzelveranstaltungsseiten

= 1.18.0 =
* **DESIGN:** Veranstaltungsarchiv im Highlights-Design
* 3-Spalten Grid-Layout (responsive: 2 Spalten auf Tablet, 1 Spalte auf Mobile)
* Event-Karten mit Bild, Datum, Titel
* Hover-Effekte: Lift + Scale + Schatten
* Schöne Pagination mit roten Buttons
* Neue CSS-Datei: archive.css

= 1.17.1 =
* **ÄNDERUNG:** News-Bereich scrollt jetzt mit (nicht mehr sticky)
* Besseres Scroll-Verhalten

= 1.17.0 =
* **FEATURE:** News-Bereich zeigt jetzt automatisch WordPress-Beiträge
* Neueste 5 Beiträge mit Bild, Datum, Titel, Auszug
* "Weiterlesen" Link zu jedem Beitrag
* Schönes Karten-Design passend zum Event-Bereich
* Erstelle Beiträge unter "Beiträge" → "Erstellen"

= 1.16.1 =
* **ÄNDERUNG:** Standard-Limit auf 5 Events reduziert
* Übersichtlichere Darstellung auf der Startseite

= 1.16.0 =
* **FEATURE:** 2-Spalten Layout für Event Programm
* Links: Events (60% Breite)
* Rechts: News-Bereich (40% Breite, sticky)
* "Alle Veranstaltungen" Link unter Events
* Standard-Limit auf 20 Events reduziert
* Neue Parameter: show_all_link, all_link_url
* Responsive: 1-Spalte auf Tablets/Mobile

= 1.15.4 =
* **BUGFIX:** program.css wird jetzt auf allen Seiten geladen
* Design funktioniert jetzt auch wenn Shortcode nicht auf Event-Seite ist
* CSS-Ladereihenfolge optimiert

= 1.15.3 =
* **DESIGN:** Event Programm im Highlights-Stil überarbeitet
* Roter Akzent-Streifen oben auf jeder Event-Karte
* Größere Bilder (180x180px) mit Rotation beim Hover
* Schönere Schatten und Animationen
* Roter Button statt gelber Text
* Titel wird rot beim Hover
* Bessere Typografie und Abstände

= 1.15.2 =
* **ÄNDERUNG:** [kh_event_program] zeigt jetzt standardmäßig kommende Events
* Nicht mehr auf aktuellen Monat beschränkt
* Neuer Parameter: limit (Standard: 50)
* month/year jetzt optional für monatliche Ansicht
* show_navigation standardmäßig false

= 1.15.1 =
* **BUGFIX:** event_program() Methode fehlte in class-shortcodes.php
* Shortcode [kh_event_program] funktioniert jetzt

= 1.15.0 =
* **DESIGN:** Dekorative rote Linien im Highlight-Widget entfernt
* Saubereres, minimalistischeres Design

= 1.14.5 =
* **DESIGN:** Dekorative rote Linien im Highlight-Widget entfernt
* Saubereres, minimalistischeres Design

= 1.14.4 =
* **BUGFIX:** Fatal Error beim Erstellen von Orten/Veranstaltern behoben
* save_meta() Methode zu KH_Venue_Meta hinzugefügt
* save_meta() Methode zu KH_Organizer_Meta hinzugefügt
* Ort und Veranstalter können jetzt wieder erstellt werden

= 1.14.3 =
* **BUGFIX:** Drop-Shadow bei Highlight-Karten weicher gemacht
* Box-Shadow von 0 10px 40px auf 0 4px 20px reduziert (weniger harte Kante)
* Hover-Shadow von 0 25px 70px auf 0 8px 30px reduziert
* Opacity von 0.15 auf 0.08 reduziert (weicher Übergang)

= 1.14.2 =
* **BUGFIX:** Button-Größen vereinheitlicht
* Tickets kaufen und Kalender-Buttons haben jetzt einheitliche Größe
* Padding: 10px 20px (statt 12px 24px)
* Font-Size: 14px (statt 16px)

= 1.14.1 =
* **BUGFIX:** PHP Deprecated Warning bei fgetcsv() behoben
* Escape-Parameter für fgetcsv() hinzugefügt (PHP 8.4+ Kompatibilität)

= 1.14.0 =
* **NEUES FEATURE:** Event Import & Export via CSV
* Admin-Seite: Veranstaltungen → Import/Export
* CSV-Export aller Events (mit/ohne vergangene)
* CSV-Import mit Update-Option für existierende Events
* Beispiel-CSV mit 12 Test-Events zum Download
* Unterstützt alle Event-Felder: Datum, Ort, Preise, Status, Kategorien, Tags, Featured Image
* Featured Images werden automatisch von URL importiert
* UTF-8 Unterstützung mit BOM

= 1.13.1 =
* **DESIGN-ÄNDERUNG:** Highlights Standard-Farbe von Gelb auf Rot geändert
* Gelber Highlight-Balken unter Titel entfernt (kein Durchstrich mehr)
* Button-Farbe: Rot (#dc143c) mit weißem Text
* Dekorative Linien: Rot statt Gelb
* Event-Karten Akzent: Rot statt Gelb

= 1.13.0 =
* **NEUES FEATURE:** Widget-Anpassungen für Farben und Texte
* Kommende Events Widget: Akzentfarbe, Textfarbe, Link-Text anpassbar
* Kommende Events Widget: "Alle ansehen"-Link optional
* Event Filter Widget: Button-Farbe und Button-Text anpassbar
* Color Picker in Widget-Einstellungen
* Individuelle Styles pro Widget-Instanz

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
