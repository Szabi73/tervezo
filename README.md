# Radix elemzés mockup

Ez a projekt egy interaktív, magyar nyelvű felhasználói felület prototípust tartalmaz, amely egy radix (születési horoszkóp) elemző eszköz lehetséges felépítését mutatja be. A layout a megadott referenciát követi, mobil és desktop nézet között válthatunk, a bolygók kiválasztásával pedig részletesebb leírás jelenik meg. Az alkalmazás mostantól kétféle megközelítésben is kipróbálható: React alapú prototípusként és teljesen önálló HTML + JavaScript nézetként.

## Fő jellemzők

- **Eszköz váltás:** A jobb felső sarokban mobil és desktop nézet között lehet váltani, a sidebar viselkedése ehhez igazodik.
- **Mentett személyek kezelése:** Modal ablakból lehet kiválasztani vagy új személyt felvenni (űrlap prototípus).
- **Radix ábra:** Bolygók ikonjaival interaktív kördiagram, amely kiemeli az aktuálisan kiválasztott elemet és leírást ad róla.
- **CTA blokk:** Letöltés és megrendelés gombok kiemelt, vizuálisan hangsúlyos panelen.
- **Teljes HTML nézet:** A `radix-static.html` fájl React nélkül is biztosítja ugyanazokat az interakciókat (modális ablakok, bolygóválasztás, eszközváltás).

## Technológiai háttér

- **React 18** kerül betöltésre CDN-en keresztül az eredeti prototípushoz (`index.html`), a komponensek JSX-ben, Babel segítségével renderelődnek.
- **Vanilla JavaScript** gondoskodik a `radix-static.html` fájlban a logikáról, így bármely statikus környezetben azonnal működik.
- **Tailwind CSS CDN** gondoskodik az elrendezésről és a modern stílusokról, további globális beállítások a `styles.css` fájlban találhatók.
- Az ikonok egyszerű Unicode / SVG elemek, így nincs szükség külső ikon csomag telepítésére.

## Fájlstruktúra

- `index.html` – A teljes alkalmazást tartalmazza: betölti a CDN könyvtárakat és definiálja a React komponenst.
- `radix-static.html` – React nélküli, tisztán HTML + JavaScript megvalósítás, minden fő funkcióval.
- `styles.css` – Alap reset és betűtípus beállítások, a Tailwind osztályok kiegészítésére.

## Futatás

1. Nyisd meg a `index.html` vagy a `radix-static.html` fájlt bármely modern böngészőben.
2. Nincs build vagy telepítési lépés, minden függőség CDN-en keresztül töltődik.
3. A statikus HTML nézet (`radix-static.html`) internetkapcsolat nélkül is használható, ha a Tailwind CDN helyett saját buildet csatolsz.

> Tipp: fejlesztés közben érdemes egy egyszerű statikus szervert indítani (például `python -m http.server`), hogy a fájlváltozásokat automatikusan követhesd.
