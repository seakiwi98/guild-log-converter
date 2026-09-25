# Guild Log Overview

A lightweight PHP application for parsing and visualizing guild battle logs with shareable statistics pages.

## Features

- 📤 Upload guild battle log files (`.txt` format)
- 📊 Automatic parsing and statistics generation
- 🔗 Shareable results with clean URLs
- 🎨 Twig-based templating system
- 🔒 Built-in security measures

## Requirements

- PHP 8.3 or newer
- Composer

## Installation

Install dependencies via Composer:

```bash
composer install
```


## Usage

### Local Development

Start the built-in PHP development server:

```shell script
php -S localhost:8000 -t public public/router.php
```


Then navigate to:

```
http://localhost:8000
```


### Uploading Logs

1. Navigate to the home page
2. Select a `.txt` log file (max 5 MB)
3. Upload and view the parsed statistics
4. Share the generated URL with your guild

### Viewing Shared Results

Results are accessible via clean URLs:

```
http://localhost:8000/{result-id}
```


## Project Structure

```
.
├── config/
│   └── ui_texts.php          # Customizable UI labels and messages
├── data/
│   └── results/              # Stored JSON results (writable by PHP)
├── public/
│   ├── index.php             # Application entry point
│   ├── router.php            # Development server router
│   └── .htaccess             # Apache rewrite rules
├── src/                      # Application source code (PSR-4: LogConv\)
├── templates/
│   └── layout.twig           # Main Twig layout template
└── composer.json
```


## Configuration

### UI Customization

Edit UI labels and error messages in:

```
config/ui_texts.php
```


### Templates

Twig templates are located in:

```
templates/
```


The main layout file is `templates/layout.twig`.

## Deployment

### Production Setup

1. **Web Server Configuration**
    - Point the document root to `public/`
    - Ensure URL rewriting is enabled (Apache/Nginx)

2. **File Permissions**
    - Make `data/results/` writable by the web server user
    - Keep `data/` outside the public web root when possible

3. **Apache Configuration**
    - The included `public/.htaccess` handles URL rewriting for clean result URLs

4. **Nginx Configuration** (example)
```
location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
```


## Security

- File upload validation (type, size, authenticity)
- Sanitized file names
- Security headers automatically sent
- Input validation and sanitization

## License

This project is provided as-is for guild management purposes.

