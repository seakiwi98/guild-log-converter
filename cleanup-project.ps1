$ErrorActionPreference = "Stop"

function Write-Utf8NoBom {
    param (
        [string] $Path,
        [string] $Content
    )

    $directory = Split-Path -Parent $Path

    if ($directory -and -not (Test-Path $directory)) {
        New-Item -ItemType Directory -Path $directory -Force | Out-Null
    }

    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText((Join-Path (Get-Location) $Path), $Content, $utf8NoBom)
}

function Remove-ProjectFile {
    param (
        [string] $Path
    )

    if (Test-Path $Path) {
        Remove-Item $Path -Force
        Write-Host "Removed $Path" -ForegroundColor DarkGray
    }
}

function Ensure-Directory {
    param (
        [string] $Path
    )

    if (-not (Test-Path $Path)) {
        New-Item -ItemType Directory -Path $Path -Force | Out-Null
        Write-Host "Created $Path" -ForegroundColor DarkGray
    }
}

Write-Host ""
Write-Host "Cleaning old PHP templates..." -ForegroundColor Yellow

Remove-ProjectFile "templates/layout.php"
Remove-ProjectFile "templates/partials/error.php"
Remove-ProjectFile "templates/partials/hidden-details.php"
Remove-ProjectFile "templates/partials/modal.php"
Remove-ProjectFile "templates/partials/share-box.php"
Remove-ProjectFile "templates/partials/summary.php"
Remove-ProjectFile "templates/partials/upload-form.php"
Remove-ProjectFile "templates/sections/guild-matchups.php"
Remove-ProjectFile "templates/sections/guild-overview.php"
Remove-ProjectFile "templates/sections/parsed-events.php"
Remove-ProjectFile "templates/sections/player-overview.php"

Write-Host ""
Write-Host "Removing one-off update scripts..." -ForegroundColor Yellow

Remove-ProjectFile "accessibility-update.ps1"
Remove-ProjectFile "centralize-ui-texts.ps1"
Remove-ProjectFile "fix-emote-encoding.ps1"
Remove-ProjectFile "migrate-to-twig.ps1"
Remove-ProjectFile "mobile-ui-update.ps1"
Remove-ProjectFile "pretty-url-update.ps1"
Remove-ProjectFile "rewrite-project.ps1"
Remove-ProjectFile "shareable-results-update.ps1"
Remove-ProjectFile "ui-emotes-update.ps1"

Write-Host ""
Write-Host "Ensuring runtime directories..." -ForegroundColor Yellow

Ensure-Directory "config"
Ensure-Directory "data"
Ensure-Directory "data/results"
Ensure-Directory "public"
Ensure-Directory "public/assets"
Ensure-Directory "src"
Ensure-Directory "templates"
Ensure-Directory "templates/partials"
Ensure-Directory "templates/sections"

Write-Host ""
Write-Host "Writing .gitignore..." -ForegroundColor Yellow

Write-Utf8NoBom ".gitignore" @'
/vendor/
/data/results/*.json
/.idea/
/*.log
.DS_Store
Thumbs.db
'@

Write-Host ""
Write-Host "Writing README.md..." -ForegroundColor Yellow

Write-Utf8NoBom "README.md" @'
# Guild Log Overview

A PHP and Twig application that parses uploaded guild battle logs and creates shareable statistics pages.

## Requirements

- PHP 5.6 or newer
- Composer

## Install

Run:

composer install

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
'@

Write-Host ""
Write-Host "Checking expected runtime files..." -ForegroundColor Yellow

$requiredFiles = @(
    "config/ui_texts.php",
    "composer.json",
    "public/.htaccess",
    "public/index.php",
    "public/results.php",
    "public/router.php",
    "public/assets/app.js",
    "public/assets/style.css",
    "src/DetailBuilder.php",
    "src/GuildLogParser.php",
    "src/ResultRepository.php",
    "src/ViewRenderer.php",
    "src/helpers.php",
    "templates/layout.twig",
    "templates/partials/error.twig",
    "templates/partials/hidden-details.twig",
    "templates/partials/modal.twig",
    "templates/partials/share-box.twig",
    "templates/partials/summary.twig",
    "templates/partials/upload-form.twig",
    "templates/sections/guild-matchups.twig",
    "templates/sections/guild-overview.twig",
    "templates/sections/parsed-events.twig",
    "templates/sections/player-overview.twig"
)

$missingFiles = @()

foreach ($file in $requiredFiles) {
    if (-not (Test-Path $file)) {
        $missingFiles += $file
    }
}

if ($missingFiles.Count -gt 0) {
    Write-Host ""
    Write-Host "Warning: These expected runtime files are missing:" -ForegroundColor Red

    foreach ($file in $missingFiles) {
        Write-Host " - $file" -ForegroundColor Red
    }

    Write-Host ""
    Write-Host "If these files are missing, rerun the Twig migration before deploying." -ForegroundColor Yellow
} else {
    Write-Host "All expected runtime files exist." -ForegroundColor Green
}

Write-Host ""
Write-Host "Refreshing Composer autoload..." -ForegroundColor Yellow

composer dump-autoload

Write-Host ""
Write-Host "Project cleanup complete." -ForegroundColor Green
Write-Host ""
Write-Host "Start locally with:" -ForegroundColor Yellow
Write-Host "php -S localhost:8000 -t public public/router.php" -ForegroundColor Cyan