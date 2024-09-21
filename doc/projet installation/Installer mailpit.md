# Mailpit

Mailpit est plus récent que MailHog et supporte le HTTPS

## Installation

```bash
# https://github.com/axllent/mailpit
# https://mailpit.axllent.org/docs/install/sendmail/
sudo bash < <(curl -sL https://raw.githubusercontent.com/axllent/mailpit/develop/install.sh)
```

## php.ini

```ini
# https://mailpit.axllent.org/docs/install/sendmail/
sendmail_path=/usr/local/bin/mailpit sendmail
```

## Lancement du serveur (https)

```bash
mailpit --ui-tls-cert /etc/apache2/ssl/dev.mshome.net.pem --ui-tls-key /etc/apache2/ssl/dev.mshome.net-key.pem
```

## Consultation des mails

URL : `https://localhost:8025/`



 
