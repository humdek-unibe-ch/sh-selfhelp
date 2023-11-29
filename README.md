# SelfHelp WebApp

The SelfHelp WebApp is a tool that allows to create a web application that serves as a platform for research experiments.
The basic concept is as follows:

Pages are organized as a collection of sections that are rendered on the page, one below the other.
Sections have different `styles` which define the appearance of the sections.
Depending on the style of a section, the section has different `fields` which define the content of the section.
The value of a field can be a simple plaintext or a collection of child sections which have their own styles and children.

Currently available styles include, but are not limited to, alert boxes, buttons, card containers, forms, media elements, tabs, lists, and support for markdown texts.

 - For information about documentation refer to [DOCUMENTS](DOCUMENTS.md)
 - For information about recent changes refer to [CHANGELOG](CHANGELOG.md)

PHP 8.1 Installation

 - `sudo add-apt-repository ppa:ondrej/php`
 - `sudo apt install php8.1`
 - `sudo apt-get install php8.1-fpm php8.1-mysql libapache2-mod-php8.1 libapache2-mod-fcgid php8.1-apcu php8.1-uopz php8.1-mbstring php8.1-intl -y`


