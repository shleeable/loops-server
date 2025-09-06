# Translation Guide

This guide explains how to add and update translations for both Laravel backend and Vue frontend in the Loops application.

**Please do not create new english translation files**

## 📁 File Structure

```
lang/
├── en/
│   ├── auth.php
│   ├── nav.php
│   ├── profile.php
│   └── ...
├── es/
│   ├── auth.php
│   ├── nav.php
│   ├── profile.php
│   └── ...
├── fr/
│   ├── auth.php
│   ├── nav.php
│   └── ...
└── de/
```

## 🌍 Adding a New Language

1. **Create the language directory:**
   ```bash
   mkdir lang/[lang-code]
   ```
   Example: `mkdir lang/de` for German

2. **Copy translation files from English:**
   ```bash
   cp lang/en/*.php lang/de/
   ```

3. **Translate the content** in each `.php` file

4. **Generate Vue I18n files:**
   ```bash
   php artisan vue-i18n:generate
   ```

5. **Rebuild frontend assets:**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

## ✏️ Updating Existing Translations

1. **Edit the translation files** in `lang/[lang-code]/[file].php`

2. **Run the generation command:**
   ```bash
   php artisan vue-i18n:generate
   ```

3. **Rebuild frontend assets:**
   ```bash
   npm run build
   ```

## 📋 Usage Examples

### In Laravel (Backend)

```php
// In controllers or views
__('nav.home')                    // "Home"
__('nav.menu.main')              // "Main Menu"
trans('auth.failed')             // "These credentials do not match our records."
trans_choice('messages.apples', 10) // Pluralization
```

### In Vue Components (Frontend)

```vue
<template>
  <nav>
    <a href="/">{{ $t('nav.home') }}</a>
    <a href="/about">{{ $t('nav.about') }}</a>
    
    <!-- With parameters -->
    <span>{{ $t('welcome.message', { name: userName }) }}</span>
    
    <!-- Pluralization -->
    <span>{{ $tc('messages.apples', appleCount) }}</span>
  </nav>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t, tc } = useI18n()

const homeLabel = t('nav.home')
</script>
```

## 🎯 Best Practices

### Translation Guidelines
- **Keep translations concise** but clear
- **Maintain consistent tone** across all translations
- **Consider context** - the same English word might need different translations in different contexts
- **Test with longer text** - some languages are significantly longer than English

### Parameters and Pluralization
```php
// Parameters
'welcome.message' => 'Welcome back, :name!',

// Pluralization
'messages.apples' => '{0} No apples|{1} One apple|[2,*] :count apples',
```

## 🔧 Troubleshooting

### Command Issues
```bash
# If the command doesn't exist
php artisan list | grep vue-i18n

# Clear cache if translations don't update
php artisan cache:clear
php artisan config:clear
```

### Frontend Issues
```bash
# Clear Vite cache
rm -rf node_modules/.vite
npm run build
```

### File Permissions
```bash
# Ensure proper permissions
chmod -R 755 lang/
chmod -R 755 resources/js/i18n/
```

## 📚 Quick Reference Commands

```bash
# Generate translations
php artisan vue-i18n:generate

# Build frontend
npm run build

# Development with hot reload
npm run dev
```

## 🎉 Complete Workflow Example

Adding Spanish navigation translations:

1. **Create/Edit the file:**
   ```bash
   nano lang/es/nav.php
   ```

2. **Add translations:**
   ```php
   <?php
   return [
       'home' => 'Inicio',
   ];
   ```

3. **Generate Vue I18n files:**
   ```bash
   php artisan vue-i18n:generate
   ```

4. **Rebuild assets:**
   ```bash
   npm run build
   ```

5. **Use in Vue:**
   ```vue
   <template>
     <nav>
       <router-link to="/">{{ $t('nav.home') }}</router-link>
     </nav>
   </template>
   ```

That's it! Your translations are now available in both Laravel and Vue components.
