# TicketBar - GLPI Plugin

> **Note:** This plugin is primarily a proof of concept and may not receive regular maintenance or updates. Use in production at your own risk.

TicketBar is a GLPI plugin that adds a quick search bar to ticket forms, allowing technicians to quickly add assets, locations, and ITIL objects to a ticket.

## Features

- **Quick search**: Integrated search bar directly in the ticket form
- **Assets**: Search and add computers, monitors, printers, network equipment, phones, peripherals
- **Locations**: Search and add locations
- **ITIL objects**: Search and add related problems and changes
- **Real-time search**: Instant results as you type
- **Simple and fast**: Add items with a single click

## Installation

1. Download the plugin
2. Extract it into the `plugins/ticketbar` folder of your GLPI installation
3. Log in to GLPI as an administrator
4. Go to Configuration > Plugins
5. Install and activate the TicketBar plugin

## Usage

1. Open a ticket (create or edit)
2. Scroll to the "Quick Search & Add Items" section
3. Type at least 2 characters to start the search
4. Click "Add" next to the desired item to add it to the ticket
5. The item is automatically linked to the ticket

## Searchable item types

- Computers
- Monitors
- Printers
- Network equipment
- Phones
- Peripherals
- Software
- Software licenses
- Locations
- Problems
- Changes
- Users
- Suppliers
- Contracts

## Compatibility

- GLPI: tested on 11.0.x

## Support

For any questions or issues, open an issue on the GitHub repository.

## Changelog

See the [CHANGELOG.md](CHANGELOG.md) file or the version history on the GLPI Store.

## Contributing

Contributions are welcome!

- Open an issue for each bug or suggestion so it can be discussed
- Follow the [GLPI plugin development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
- Work on a dedicated branch in your fork
- Open a Pull Request (PR) that will be reviewed by a maintainer

Thank you for helping improve the plugin!

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.