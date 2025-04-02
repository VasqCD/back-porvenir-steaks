# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {SCRIBE_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Puedes obtener tu token realizando una petición POST a `/api/login` con tus credenciales de usuario. También puedes registrarte en `/api/register`.
