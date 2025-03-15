# Health

![Build Status](https://github.com/cli-ish/moodle-tool_health/actions/workflows/moodle-ci.yml/badge.svg?branch=master)

The Tool Health plugin is designed to enhance the stability and reliability of your Moodle environment by proactively
checking for a variety of potential configuration issues and errors. This plugin runs multiple problem classes to
identify and diagnose common problems, ensuring that your Moodle instance remains healthy and performs optimally. By
integrating Tool Health into your Moodle setup, you can streamline maintenance tasks and quickly address any
configuration-related issues that may arise.

## Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually

The plugin can be also installed by putting the contents of this directory to

    {dirroot}/admin/tool/health

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License

2025, Vincent Schneider

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program. If not, see <https://www.gnu.org/licenses/>.
