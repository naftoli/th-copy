# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Tzivos Hashem is a monorepo containing a gamification/task management platform with multi-language support (Hebrew, English, French). It consists of a PHP backend and two React frontends.

## Architecture

### Projects

- **base-commander/** - Legacy React frontend (React 16, Webpack 3, Create React App). Deployed to `/new` path.
- **front-end/** - Modern React frontend (React 19, Vite). Mirrors base-commander structure with updated dependencies.
- **mashpia.com/** - PHP 7.2 backend with Apache. Contains API endpoints and legacy admin system.

### Frontend Structure (shared between both React apps)

```
src/
  api/          - API client with fetch wrapper, auth headers
  components/   - Reusable UI components
  pages/        - Page containers (login, home, missions, rewards, etc.)
  store/        - Redux state (login, home, missions, rewards, payments, base)
  functions/    - Utility functions
  data/         - Static data/menu configuration
  heDatePicker/ - Custom Hebrew date picker
  styles/       - SCSS stylesheets
```

### State Management

- **base-commander**: Classic Redux with thunk middleware
- **front-end**: Redux Toolkit with createSlice
- Store resets on logout, preserves login state on account change

### API Communication

- Auth via cookies: `admin_id` and `admin_auth` sent as `Authorization: Legacy {id}-{auth}` header
- Base URL configured via `LEGACY_URL` constant (empty in production, `//localhost` in dev)
- API endpoints at `/api/*` served by PHP backend

## Common Commands

### base-commander (legacy frontend)
```bash
cd base-commander
yarn install
yarn start    # Development server
yarn build    # Production build
yarn test     # Run Jest tests
```

### front-end (modern frontend)
```bash
cd front-end
yarn install
yarn dev      # Vite development server
yarn build    # Production build
yarn lint     # ESLint
```

### PHP Backend
```bash
cd mashpia.com
composer install
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests  # Run tests
```

### Full Build & Deploy
```bash
./build-react.sh    # Builds base-commander and deploys to mashpia.com/public/new
```

## Path Aliases (front-end/Vite)

Import paths are aliased to `src/` subdirectories:
- `api`, `components`, `data`, `functions`, `img`, `pages`, `store`, `styles`, `tests`, `heDatePicker`

Example: `import api from 'api/api'` resolves to `src/api/api.js`

## Deployment

CircleCI pipeline on push to `master` or `testing` branches:
1. Builds base-commander React app
2. Deploys via SSH to `test.mashpia.com` (testing branch) or `mashpia.com` (master)
3. Runs `deploy/deploy.sh` which moves build to `/mashpia.com/public/new`

## Key Files

- `base-commander/src/components/constants.js` - Shared constants including `LEGACY_URL`
- `base-commander/src/api/api.js` - API wrapper with auth handling
- `base-commander/src/store/index.js` - Redux store configuration
- `front-end/vite.config.js` - Vite configuration with path aliases
