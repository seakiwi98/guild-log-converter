# Guild Log Overview

A PHP and Twig application that parses uploaded guild battle logs and creates shareable statistics pages.

## Requirements

- PHP 8.3 or newer
- Composer

## Install

Run:

```bash
composer install
``` 

## Run locally

Run:

php -S localhost:8000 -t public public/router.php

Then open:

http://localhost:8000

## UI Texts

All editable UI labels are stored in:

config/ui_texts.php

## Templates

Twig templates are stored in:

templates/

The main layout file is:

templates/layout.twig

## Shareable Results

Uploaded logs are parsed and stored as JSON files in:

data/results/

The generated result page uses a clean URL:

http://localhost:8000/{result-id}

## Deployment Notes

- Point the web server document root to public/
- Keep data/ outside the public web root when possible
- Make sure PHP can write to data/results/
- For Apache, public/.htaccess contains the rewrite rule for pretty result URLs