#!/bin/sh

# Vérifie que les clés existent
if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
  echo "JWT keys not found, generating..."
  php bin/console lexik:jwt:generate-keypair --overwrite
fi


# Fixe les permissions
chown www-data:www-data config/jwt/private.pem config/jwt/public.pem
chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem


# Démarre le serveur
exec symfony server:start --no-tls --allow-http --port=8000 --allow-all-ip
