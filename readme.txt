=== WooRechnung ===
Contributors: ZWEISCHNEIDER
Donate link: https://woorechnung.com/
Tags: woocommerce, fastbill, automatic, monsum, debitoor, billomat, easybill, sevdesk, shipcloud, abrechnung, rechnung, rechnungen, lieferschein, lieferscheine
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooRechnung ermöglicht Ihnen Rechnungen, Gutschriften, Kunden und Versandmarken aus WooCommerce direkt in vielen Providern automatisch zu erzeugen.

== Description ==

Erstelle direkt Rechnungen über WooCommerce oder verbinde es mit **Billomat**, **Debitoor**, **easybill**, **FastBill**, **Monsum by FastBill**, **sevDesk** oder **shipcloud** – WooRechnung ermöglicht Ihnen Rechnungen, Gutschriften, Kunden und Versandmarken aus dem WordPress Plugin WooCommerce direkt zu erzeugen und aktualisieren. Dadurch erhalten Sie die Vorteile aus beiden Welten und sparen dabei sehr viel Zeit!

**Automatisierte Abläufe:**
Erstellen und Aktualisieren von Kundendaten, Erzeugen von Rechnungen, Lieferscheinen und Gutschriften bei Widerruf, Bereitstellen von Rechnungen als Download und erzeugen von Versandmarken – alles direkt in WooCommerce.

**Multiple Shops:**
Verbinden Sie beliebig viele WooCommerce Shops mit WooRechnung und behalten Sie den Überblick über Ihre gesamte Rechnungsstellung.

**Individuelle Layouts:**
Wählen Sie aus Ihren Rechnungsvorlagen und erstellen Sie stets zu Ihren hinterlegten Einstellungen passende und ansprechende Rechnungen.

- Kostenlos bis zu 25 Rechnungen und 25 Versandmarken / Monat
- 5,- € bei bis zu 100 Rechnungen und 100 Versandmarken / Monat
- 15,- € bei bis zu 500 Rechnungen und 500 Versandmarken / Monat
- 25,- € bei unbegrenzt vielen Rechnungen und Versandmarken / Monat + priorisiertem Support

**(Alle Preise sind inkl. MwSt)**

Sie benötigen einen WooRechnung Account. Registrieren Sie Ihren Account auf [woorechnung.com](https://woorechnung.com/ "Einfach Rechnungen über Billomat, Debitoor, easybill, FastBill, Monsum by FastBill oder sevDesk erzeugen")

== Installation ==

1. Laden Sie die Plugin-Daten in Ihr Verzeichniss `/wp-content/plugins/woorechnung`, oder installieren Sie das Plugin direkt über das WordPress Plugin-Management.
2. Aktivieren Sie WooRechnung über den Menüpunkt 'Plugin'
3. Klicken Sie auf WooCommerce -> WooRechnung um Ihre Einstellungen vorzunehmen. Den Lizenzkey finden Sie in Ihrem WooRechnung Account.
4. Passen Sie die Einstellungen nach Ihren Vorstellungen an.

== Frequently Asked Questions ==

= Benötige ich einen WooRechnung Account? =

Ja. Registrieren Sie sich unter https://woorechnung.com und folgen Sie den Setup-Prozess.

= Kann ich kostenlos Rechnungen erzeugen? =

Ja! Bis zu 25 Rechnungen im Monat ist WooRechnung kostenlos. Wenn Sie mehr Rechnungen schreiben möchten, können Sie Ihren Account jederzeit upgraden.

= In welchem Format müssen die Preise angegeben werden? =

Bitte die Preise immer in Netto angeben, da es oft zu Rundungsproblemen führt wenn WooCommerce die Bruttopreise zuerst in Netto umrechnet.

== Screenshots ==

1. WooRechnung Dashboard

== Changelog ==

= 1.0.0 (07.07.2017) =
* Neu: Verbessertes logging bei Debitoor
* Fix: 1 Cent Rungundsprobleme sind nun behoben
* Fix: Doppelte Statusmeldungen beim speichern der WordPress Einstellungen

= 0.9.14 (22.06.2017) =
* Neu: Option damit eine Versandmarke nach dem Erstellen direkt in einem neuen Tab geöffnet wird
* Neu: Automatische Berechnung des Gewichtes für Versandmarken anhand der Produkte
* Neu: Default Preset kann gewählt werden. So wird die Versandmarke unten immer automatisch damit befüllt
* Fix: sevDesk Login Methode funktioniert nun wieder
* Fix: Undefined Fehlermeldungen bei WooCommerce 3 behoben

= 0.9.13 (14.06.2017) =
* Fix: Kompatibilitätsproblem mit WooCommerce 3.0.8

= 0.9.11 (12.06.2017) =
* Neu: Du kannst bei sevDesk nun ein Zahlungsziel auswählen
* Neu: Wir haben die Kleinunternehmerregelung zu "Ohne Anbieter" hinzugefügt
* Neu: Wir haben die Schweiz als Land für Debitoor hinzugefügt
* Neu: Wir haben Österreich als Land für Debitoor hinzugefügt
* Fix: Wir erzeugen keine doppelten Rechnungen mehr wenn Status der Rechnung als auch des E-Mail Versandes gleich sind
* Fix: sevDesk: Intro- und Outrotext funktioniert nun mit Absätzen
* Fix: easybill: Länder werden nun richtig gespeichert
* Fix: Wir erzeugen keine Kunden mehr mit leeren E-Mailadressen
* Fix: Debitoor: MwSt werden nun pro Land einzeln betrachtet
* Fix: Ohne Anbieter: Bezeichnung Bestellung in Rechnung geändert

= 0.9.10 (01.03.2017) =
* Neu: Shipcloud Versicherungsbetrag kann eingegeben werden

= 0.9.9 (28.02.2017) =
* Neu: Die Rechnungsnummer von "Ohne Anbieter" kann nun durch weitere Variablen verfeinert werden
* Neu: VAT Nummern aus dem Plugin "WooCommerce EU VAT Number" von WooThemes
* Fix: Log "Permission" Probleme behoben
* Fix: Debitoor funktioniert nun auch mit anderen Währungen als €

= 0.9.8 (27.02.2017) =
* Fix: Undefined Variable

= 0.9.7 (23.02.2017) =
* Neu: sevDesk Kleinunternehmerregelung kann nun aktiviert werden
* Neu: sevDesk Bruttorechnungen können ab jetzt erzeugt werden
* Neu: bei FastBill werden nun mehr Zahlungsarten erkannt und richtig zugewiesen
* Fix: Versicherungs Datum wird nun wieder unter den Produkten angezeigt

= 0.9.6 (20.02.2017) =
* Neu: VAT-Nummer zu "ohne Anbieter" hinzugefügt
* Fix: Logger Fehlermeldung beim ersten Nutzen entfernt
* Fix: Versicherung wird nun über "Fees" hinzugefügt
* Fix: sevDesk Titel "Invoice" ind "Rechnung" geändert
* Fix: Text Bestellung in Bestellnummer geändert
* Fix: API Calls beschleunigt

= 0.9.5 (26.01.2017) =
* Neu: Debitoor speichert nun auch die VatID (Plugin: WooCommerce EU VAT Assistant)
* Fix: Debitoor Rechnungsnummer als Dateinamen für E-Mail und Download der Rechnungen

= 0.9.4 (09.01.2017) =
* Neu: Sollten Rechnungen zu dem Zeitpunkt des E-Mail Versandes noch nicht erzeugt worden sein, werden diese nun erzeugt

= 0.9.3 (05.01.2017) =
* Neu: Rechnungen können nun wie früher an bestehende WooCommerce E-Mails angehangen werden. Dazu gibt es eine neue Option in den Rechnungseinstellungen
* Neu: Du findest deine Rechnungen nun in deinem woorechnung.com Account
* Fix: Billomat: Rechnungsdatum funktioniert nun wie gewollt
* Fix: Debitoor: Produkte können nun wieder aktuallisiert werden
* Fix: Debitoor: z.Hd. wird nur noch gezeigt sollte auch ein Vor- / Nachname angegeben worden sein
* Fix: Debitoor: Einige Probleme mit mehreren MwSt. Sätzen auf einer Rechnung wurden behoben
* Fix: Easybill: ZIP Code wird nun wieder richtig gespeichert
* Fix: Easybill: Berechnung bei Brutto Preisen funktioniert nun wieder

= 0.9.2 (02.12.2016) =
* Neu: Lokale Log Datei für bessere Supportmöglichkeiten
* Fix: API Key lässt keine Leerzeichen mehr zu
* Fix: FastBill Adress2 wird nicht mehr in das Dokument eingefügt wenn diese leer ist

= 0.9.1 (11.11.2016) =
* Fix: Rechnungen werden nun im TEMP Ordner des Systems geschrieben um Schreibfehler zu vermeiden
* Fix: Wording

= 0.9.0 (02.11.2016) =
* Neu: E-Mails werden ab jetzt über eine eigene E-Mail verschickt. Somit beheben wir das Problem das bei einigen Shops die Rechnung nicht an E-Mails angehangen wurde. Ihr könnt die E-Mail selbständig Layouten. Geht dazu unter WordPress - WooCommerce - WooRechnung auf Rechnungs E-Mail.
* Fix: Es wurden Probleme mit der Verbindung zu sevDesk gelöst
* Fix: Mit Billomat können nun auch andere Währungen als €genutzt werden

= 0.8.5 (28.09.2016) =
* Fix: Manche Plugins verändern Preise von 0 zu NAN was zu problemen führte

= 0.8.4 (26.09.2016) =
* Fix: Für Debitoor Kunden die als Hauptsitz nicht Deutschland haben, wird mit diesem Fix die Funktionalität von WooRechnung ermöglicht

= 0.8.3 (23.09.2016) =
* Fix: gzip entfernt da es bei einigen Systemen Probleme erzeugt (Kryptische Zeichen wurden angezeigt)

= 0.8.2 (29.08.2016) =
* Neu: Fees werden nun auf der Rechnung angezeigt

= 0.8.1 (29.08.2016) =
* Neu: Mehr Länder zur Auswahl für Versandmarken
* Fix: "Undefined Index" entfernt

= 0.8.0 (24.08.2016) =
* Neu: Gutscheine erscheinen nun als Posten auf der Rechnung
* Neu: Das Plugin "WooCommerce Pay for Payment" von Jörn Lund ist ab jetzt kompatibel mit WooRechnung
* Fix: Preisberechnung verändert so das Gutscheine nicht mehr von den jeweiligen Post abgezogen werden sondern als eigener Posten auf der Rechnung erscheint

= 0.7.7 (22.08.2016) =
* Neu: Produktkurzbeschreibung kann nun auf der Rechnung gespeichert werden
* Neu: Anrede und Zusatzadresse werden gespeichert
* Fix: FastBill: Versand-Land wird nun mit gespeichert

= 0.7.6 (17.08.2016) =
* Fix: sevDesk Währungen werden nun übernommen
* Fix: Rechnungen werden nun auch wieder mit der standard Mailfunktion verschickt (Wir empfehlen dennoch den Versand per SMTP Plugin z.B. WP Mail Bank)
* Fix: Besseres Debug Log
* Fix: easybill probleme mit kleinere Paketen gelöst
* Fix: Versandkosten berechnung nach Update auf WooCommerce 2.6 gefixt

= 0.7.5 (01.08.2016) =
* Neu: Im kostenlosen Tarif haben wir die Versandmarken von 5 auf 25 geändert
* Neu: sevDesk Produkte können nun gespeichert und bearbeitet werden
* Neu: Das Limit kann nun aufgehoben werden so dass mehr Rechnungen oder Versandmarken einzeln abgerechnet werden können
* Fix: Debitoor Lieferscheine können nun auch für andere Länder als Deutschland ausgestellt werden
* Fix: Fehlermeldungen von Debitoor und sevDesk vereinfacht
* Fix: Debitoor Rechnungserstellung nach Großbritannien nun möglich

= 0.7.4 (18.07.2016) =
* Neu: FastBill kann nun pro Land ein eigenes Template nutzen
* Fix: Rechnungsicon

= 0.7.3 (18.07.2016) =
* Neu: E-Mail Status kann nun selbständig gewählt werden
* Neu: Adresse 2 wird mit übergeben
* Fix: "Ohne Anbieter" Berechnung

= 0.7.2 (16.07.2016) =
* Neu: Rechnungen können nun in jedem Status manuell erzeugt werden
* Neu: Bei "Ohne Anbieter" kann die Währung nun geändert werden
* Neu: Shipcloud kann nun WooRechnungs Kunden direkt erkennen und besser Supporten
* Neu: Bei Debitoor kann das Zahlungsdatum frei gewählt werden
* Neu: Bei Debitoor wird der Versand bei verschiedenen MwSt Sätzen auf einer Rechnung nun nach deutschem Recht gesplittet
* Fix: FastBill UNIT_PRICE

= 0.7.1 (08.07.2016) =
* Neu: Debitoor Lieferscheine können nun automatisch mit erzeugt werden.

= 0.7.0 (08.07.2016) =
* Neu: sevDesk Anbindung
* Fix: Undefined index

= 0.6.11 (04.07.2016) =
* Neu: Status "Wartend" als Rechnungsstatus hinzugefügt
* Neu: Bei versandmarken kann optional das E-Mail Feld frei gelassen werden
* Fix: Bei den Debitoor Kundenakten werden keine leeren Kundennummern gespeichert
* Fix: UPS wird nun bei der Versandmarkenerzeugung vorausgewählt
* Fix: Bei EasyBill konnten keine Rechnungen erzeugt werden

= 0.6.10 (28.06.2016) =
* Fix: Fehlerbehebung aus Version 0.6.9

= 0.6.9 (28.06.2016) =
* Neu: Unterstüzung des Plugins "Shipping Details for WooCommerce" von "PatSaTECH". Der Trackingcode und der Versandanbieter werden automatisch ausgefüllt.
* Fix: Undefined index

= 0.6.8 (25.06.2016) =
* Neu: Multilanguage Support
* Fix: Die Produktbeschreibung wird nur noch bei dem richtigen Setting mit übergeben

= 0.6.7 (22.06.2016) =
* Neu: Einheit wird mit an die Rechnung übergeben
* Neu: Unterstüzung für Monsum (ehemals FastBill Automatic)
* Neu: Debitoor - Schlußtext mit Variablen hinzugefügt (Auch für Kleinunternehmer Klausel geeignet)
* Neu: Ohne Anbieter - Artikelbeschreibung hinzugefügt
* Fix: Ohne Anbieter - Logos werden nun resized

= 0.6.6 (18.06.2016) =
* Fix: Variationsproblem seit WooCommerce 2.6.0 jetzt behoben

= 0.6.5 (17.06.2016) =
* Neu: Bulk Download der Rechnungen
* Neu: Unterstützung von "WooCommerce Order Status Manager"

= 0.6.4 (15.06.2016) =
* Neu: Versandmarken Vorlagen für schnelleren Versand
* Fix: Einige kleine Änderungen für WooCommerce 2.6.0

= 0.6.3 (13.06.2016) =
* Neu: Nun können Rechnungen auch ohne Anbieter erzeugt werden

= 0.6.2 (30.05.2016) =
* Neu: FastBill Introtext kann mit Platzhaltern versehen und überschrieben werden
* Neu: WooRent integration
* Neu: Die Verbindung zwischen WooRechnung und WordPress kann nun getestet werden
* Fix: FastBill Zahlungsarten werden nun richtig zu den Kunden gespeichert

= 0.6.1 (18.05.2016) =
* Neu: DEBUG Konsole bei Fehlermeldungen

= 0.6.0 (16.05.2016) =
* Neu: Rechnungs- / Zahlungsstatus pro Zahlungsart wählbar
* Neu: Rechnungen können ab jetzt auch als Entwurf gespeichert werden (Debitoor & FastBill)
* Fix: Briefporto über shipcloud
* Fix: TaxEnabled Fehler bei Debitoor behoben

= 0.5.10 =
* Neu: Deutsche Post AG (Briefe und Buchsendungen) können nun per shipcloud verschickt werden

= 0.5.9 =
* Fix: Rechnungen konnten nicht erzeugt werden. Ist nun wieder gefixt. Entschuldigt!

= 0.5.8 =
* Neu: FastBill - Rechnungen werden als Bezahlt markiert

= 0.5.7 =
* Neu: Das Rechnungsdatum kann in den Einstellungen geändert werden (Tag der Bestellung oder Tag der Rechnungserzeugung)
* Neu: Versandkosten-Artikelnummer ist nun änderbar
* Fix: Versandkostenberechnung mit unterschiedlichen "Germanize" Plugins gefixt

= 0.5.6 =
* Fix: Automatische Rechnungen werden nun auch erzeugt, wenn die Einstellungen noch nicht gespeichert wurden

= 0.5.5 =
* Fix: Rechnungen werden nun wieder automatisch erzeugt

= 0.5.4 =
* Neu: Weitere Länder zu den Versandmarken hinzugefügt
* Fix: Schnellere Ladezeiten

= 0.5.3 =
* Aktion: Wir haben die Preise um -50% verringert!
* Fix: Einige Anpasungen

= 0.5.2 =
* Fix: Bessere Fehlermeldungen für Rechnungen und shipcloud

= 0.5.1 =
* Neu: Rechnungserstellung kann nun deaktiviert werden falls gewünscht
* Fix: Splittung der Einstellungen in eigene Tabs

= 0.5.0 =
* Neu: shipcloud

= 0.4.5 =
* Neu: Prefix und Suffix für die Bestellnummer
* Neu: Debitoor - Lieferadresse kann mit in den Rechnungs-Hinweis gespeichert werden
* Neu: Vorbereitungen für shipcloud: Dieses Feature folgt in der nächsten Version
* Fix: Versandkosten Artikelnummer auf "vk" reduziert

= 0.4.4 =
* Fix: Leerzeichen vor und nach dem Lizenzkey werden nun ignoriert
* Fix: Das Rechnungsicon in der Bestelübersicht wird nun auch bei anderen Stati als Fertiggestellt angezeigt
* Fix: Die Rechnung wird nun mit der E-Mail versendet, zu der die Rechnung erzeugt wird

= 0.4.3 =
* Neu: FastBill Brutto-Rechnung
* Neu: Billomat Anbindung
* Neu: easybill Anbindung

= 0.4.2 =
* Neu: Es werden keine Rechnungen für 0€ Bestellungen erstellt

= 0.4.1 =
* Fix: Artikelnummer bei Variationen wird nun auf der Rechnung angezeigt
* Fix: Bei mehreren Steuersätzen auf einer Rechnung werden die Versandkosten nun anteilig aufgeteilt

= 0.4.0 =
* Fix: Undefined index Fehler
* Neu: Der Variantentitel kann nun als Produktbeschreibung an die Rechnung übergeben werden
* Neu: Die Lieferadresse wird nun in FastBill zu den Kundendaten gespeichert

= 0.3.9 =
* Fix: Debitoor - Rundungsfehler behoben
* Neu: Nur noch ein request pro Rechnungserstellung
* Neu: Debitoor - Produkte können nun gespeichert und bearbeitet werden
* Neu: Artikelnummer wird mit übertragen
* Neu: Zusätzlich zum Produktnamen wird nun auch die Produktbeschreibung gespeichert
* Neu: Option um Produktbeschreibung anzupassen

= 0.3.8 =
* Fix: "unexpected end of file" behoben

= 0.3.7 =
* Fix: Bestellnummer

= 0.3.6 =
* Neu: Nun speichert WooRechnung die Bestellnummer zu den Rechnungen

= 0.3.5 =
* Fix: MwSt. Berechnung

= 0.3.4 =
* Rechnungen automatisch versenden

= 0.3.3 =
* Erstelle, downloade und storniere Rechnungen automatisch bei der Bearbeitung deiner WooCommerce bestellungen
* Rechnungs-Provider: Debitoor, FastBill und Automatic by FastBill

== Upgrade Notice ==

= 0.3.3 =
Erste Version