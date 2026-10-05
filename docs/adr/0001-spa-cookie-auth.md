# 0001: Cookie-based Sanctum SPA auth

Status: accepted

## Context
React SPA and Laravel API are separate apps on the same site (localhost in dev).

## Decision
Use Breeze (API stack) with Sanctum SPA mode: session cookie + CSRF token.

## Consequences
- httpOnly cookie, so tokens are not readable by JS (XSS-safer than localStorage).
- Frontend and API must share a registrable domain in production (e.g. app.x.com + api.x.com).
- Bearer tokens remain available for mobile/third-party clients.