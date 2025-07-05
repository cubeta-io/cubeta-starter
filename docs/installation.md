# Installation

This guide will walk you through the process of installing and setting up Cubeta Starter in your Laravel project.

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- Composer

## Basic Installation

In your project root directory, open the terminal and run:

```bash
composer require cubeta/cubeta-starter
```

This will add the package to your project's dependencies.

## Complete Installation

After installing the package, you have two options for setting up your project:

1. Use the GUI interface provided by the package
2. Use terminal commands to perform the required actions

### Using the GUI

The package provides a user-friendly graphical interface to help you set up your project:

1. Navigate to `http://yourdomain.com/cubeta-starter` in your browser
   - For local development with XAMPP: `http://localhost/project-directory/public/cubeta-starter`

2. You'll see a complete installation interface (available only in development environments)

3. Select your preferred frontend stack:
   - **Blade, Bootstrap, jQuery**: Traditional Laravel frontend
   - **Inertia, React, TypeScript, Tailwind**: Modern SPA-like experience
   - **No Frontend (API Only)**: For backend API development

4. Based on your selection, you'll see additional installation options:
   - **Install For API Usage**: Sets up classes and traits for API-based CRUDs
   - **Install For Web Usage**: Sets up classes and traits for web-based CRUDs
   - **Install Both**: Sets up both API and web files
   - **Install npm packages**: Installs frontend dependencies for your chosen stack
   - **Install React TS Stack Tools**: Sets up helper classes for React/TypeScript development
   - **Install React/TypeScript packages**: Installs npm packages for React development

### Using Terminal Commands

If you prefer using the command line, you can use these commands for installation:

```bash
# For API development
php artisan cubeta:install api

# For web development
php artisan cubeta:install web

# For web packages (Bootstrap, jQuery, etc.)
php artisan cubeta:install web-packages

# For React/TypeScript development
php artisan cubeta:install react-ts

# For React/TypeScript packages
php artisan cubeta:install react-ts-packages
```

> **Note**: You can generate code for one frontend stack alongside the API stack. When we refer to "web generating," this means generating for either Blade or React.ts stacks depending on your selection.

> **Note**: During installation, two route files will be generated (`protected` and `public`) based on your usage (web or API) in the `routes/v1/{selected-usage}/` directory. These will be registered in your route service provider.

## Important Considerations

> **Warning**: Installing API or web components is critical for making the generated endpoints or pages work properly.

> **Warning**: Using the package GUI for installation will override any existing files with the same name and directory. If you want more control, use the terminal commands with or without the `--force` flag:
> ```bash
> php artisan cubeta:install api --force
> ```

> **Tip**: It's good practice to ensure your project has a Git repository that tracks all your changes, as the package will generate a significant number of files.

## What Gets Installed

### Accepted Language Middleware

The package will publish a new middleware to handle your application localization and register it in your middleware aliases under the key `locale` in `/app/Http/Kernel.php`.

### CubetaStarterServiceProvider

When installing the Blade web stack, the package will publish a new service provider and register it within the `providers` key in your `/bootstrap/providers.php` config file. This service provider registers the published Blade components.

### HandleInertiaRequests Middleware

If you choose the Inertia.js stack, the package will publish and register the `HandleInertiaRequests` middleware class in your `web` middleware group in `/app/Http/Kernel.php` as part of the [Inertia.js installation process](https://inertiajs.com/server-side-setup#middleware).