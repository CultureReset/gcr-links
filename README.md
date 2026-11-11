# gcr-links

The May 2026 copy of the Gulf Coast Radar static front end: business link cards, menus, booking, reviews, rides and a directory, with the admin editors behind them.

The same page set as [`cybercheck-links-`](https://github.com/CultureReset/cybercheck-links-), at an earlier point. The last commit pointed every API call at the Gulf Coast Radar API of that time. Superseded by [`gcr-unified`](https://github.com/CultureReset/gcr-unified) and [`gcr-api-clean`](https://github.com/CultureReset/gcr-api-clean); nothing runs from it.

## How it works

Every page is a plain HTML file (`card.html`, `daily-menu.html`, `booking.html`, `boat-rental-booking.html`, `charter-booking.html`, `directory.html`, `businesses.html` and more) with shared `components/`, `css/` and `js/`. `vercel.json` maps clean URLs such as `/reviews/:site_id` and `/rides` to them. The admin pages (`admin-editor.html`, `admin-menu-editors.html`, `dashboard.html`) edit the same data.
