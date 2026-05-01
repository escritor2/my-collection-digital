# Fase 1 - Checklist de Ambiente e Auth

## Backend (.env)
- `APP_URL` deve apontar para a URL real da API.
- `SANCTUM_STATEFUL_DOMAINS` deve incluir frontend em todos os ambientes.
- `CORS_ALLOWED_ORIGINS` deve listar os domínios autorizados do frontend.
- Em produção HTTPS:
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_SAME_SITE=none` (quando frontend e API forem cross-site)

## Frontend (.env)
- `NUXT_PUBLIC_API_BASE_URL`:
  - vazio no desenvolvimento local para inferência automática;
  - explícito em staging/prod para evitar ambiguidades.
- `NUXT_PUBLIC_APP_ENV` definido como `development|staging|production`.

## Comandos pós alteração de ambiente
- Backend: `php artisan optimize:clear`
- Frontend: reiniciar `npm run dev`

