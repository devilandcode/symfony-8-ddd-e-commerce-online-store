# Symfony E-commerce Store

> **Work in progress**

An e-commerce online store built with Symfony 8, following Domain-Driven Design (DDD) principles. EasyAdmin will be used for content management.

## Admin

After configuring the database and applying migrations, create the first administrator:

```bash
php bin/console doctrine:migrations:migrate
php bin/console app:create-admin-user
```

The admin menu includes a Settings page with Contacts, Content, and Metrics tabs. Each section is stored as a separate JSON payload and is accessed through the `StoreSettings` domain and application layers.
