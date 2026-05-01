# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a full-stack LAMP application with a React frontend undergoing active migration. It consists of:

- **`/mashpia.com`** — PHP backend (Apache/MySQL), the primary server-side application
- **`/front-end`** — Modern React 19 + Vite frontend (active, replaces base-commander)
- **`/base-commander`** — Legacy Create React App frontend (being phased out, do not add new features here)
- **`/pdf-service`** — Standalone Express + Puppeteer service for PDF generation

## Commands

### Modern Frontend (`/front-end`) — Node 22.x required
```bash
npm run dev        # Vite dev server
npm run build      # Production build (outputs to /mashpia.com/public/new via build-react.sh)
npm run lint       # ESLint
npm run preview    # Preview production build
```

### Legacy Frontend (`/base-commander`) — Yarn
```bash
yarn start    # Development server
yarn build    # Production build
yarn test     # Jest tests
```

### PHP Backend (`/mashpia.com`)
```bash
composer install
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```

### Full Production Build (root)
```bash
./build-react.sh   # composer install → yarn build → deploys to /public/new
```

## Architecture

### Frontend (React)

The modern `/front-end` app uses Vite with path aliases — import from `api/`, `components/`, `pages/`, `store/`, `functions/`, `styles/`, `data/`, `img/`, `heDatePicker/` directly without relative paths.

State is managed with Redux Toolkit, organized into domain slices under `src/store/` (base, home, login, missions, payments, rewards). API calls use Axios from `src/api/`.

The app serves at `/vite/` base path (configured in `vite.config.js`).

**Hebrew calendar support** is a core feature — the app uses `jewish-dates-core`, `react-jewish-datepicker`, and a custom `heDatePicker` component for Hebrew date handling.

### Backend (PHP)

The PHP backend under `/mashpia.com/public/` is organized into domain modules: `accounting/`, `reporting/`, `registration/`, `auction/`, `donate/`, `mission_report/`, `tickets/`, `tasks/`, `rewards/`, `medals/`. Core app logic lives in `public/app/`.

Key dependencies:
- **ORM:** php-activerecord
- **Payments:** Authorize.Net
- **Email:** PHPMailer
- **Caching/Sessions:** Redis (Predis)
- **PDF (server-side):** FPDF/FPDI, DocRaptor, or the Node `/pdf-service`
- **Logging:** Monolog

### PDF Service (`/pdf-service`)

Standalone Express server using Puppeteer (HTML→PDF) and pdf-lib (merge/manipulate). Has optional Redis integration. Run independently from the main PHP app.

### Deployment

CircleCI (`.circleci/config.yml`) handles CI/CD:
- Staging: `test.mashpia.com:522`
- Production: `ssh.mashpia.com:522`
- Deploy script: `/deploy/deploy.sh`

## Business Rules Documentation

`AGENT.md` at the repo root defines an agent role for extracting business rules from legacy code. When asked to document what the system *decides* or *enforces*, follow the format in that file: plain-language rules labeled by module, with source file references.
