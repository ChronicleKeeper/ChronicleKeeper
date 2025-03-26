#!/bin/sh
set -e

# Only configure auth if both username and password are provided
if [ -n "$BASIC_AUTH_USER" ] && [ -n "$BASIC_AUTH_PASSWORD" ]; then
    # Create Caddy auth snippet
    cat > /etc/caddy/auth.conf << EOF
    @auth {
        not path /health* /metrics*
        expression {env.APP_ENV} == 'prod'
    }
    handle @auth {
        basic_auth {
            ${BASIC_AUTH_USER} ${BASIC_AUTH_PASSWORD}
        }
    }
EOF
else
    # Create empty file when no credentials are provided
    cat > /etc/caddy/auth.conf << EOF
    handle /nothing_all_invalid/* {
        # Just added content because the file must not be empty
    }
EOF
fi
