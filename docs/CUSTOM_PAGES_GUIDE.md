# Custom Pages Guide

This document explains how custom pages are implemented in this project and how the page seeder works.

## Overview

Custom pages are stored in the `pages` table and managed from Filament. Public users can open each page by visiting a URL that includes the page `slug`.

Flow summary:

1. Admin creates/updates a page in Filament.
2. Data is saved in the `pages` table.
3. Public route `/pages/{slug}` loads the page.
4. `PageController` fetches an active record and returns an Inertia page.
5. The Inertia `Page` component renders the HTML content.

## Core Files

- `app/Models/Page.php`
- `app/Filament/Resources/Pages/PageResource.php`
- `app/Filament/Resources/Pages/Schemas/PageForm.php`
- `app/Filament/Resources/Pages/Tables/PagesTable.php`
- `app/Http/Controllers/PageController.php`
- `routes/tenant.php`
- `resources/js/themes/default/pages/Page.tsx`
- `database/seeders/PageSeeder.php`
- `database/migrations/tenant/2025_11_27_000000_create_pages_table.php`
- `database/migrations/tenant/2025_11_27_000001_add_arabic_columns_to_pages_table.php`

## Database Structure

The `pages` table contains:

- `id`
- `title`
- `slug` (unique)
- `content` (long text)
- `is_active` (boolean, default `true`)
- `created_at`, `updated_at`

## Filament Resource Behavior

`PageResource` provides standard CRUD pages in Filament:

- index (list)
- create
- view
- edit

Form behavior (`PageForm`):

- Uses tabs for content fields.
- `title` is required.
- `slug` is required and unique.
- While creating, `slug` is auto-generated from `title` on title blur.
- `content` is required and edited with `RichEditor`.
- `is_active` defaults to `true`.

Table behavior (`PagesTable`):

- Search by `title` and `slug`.
- Show `is_active` as a boolean icon.
- Includes view/edit row actions and bulk delete.

## Public Page Rendering

Route definition:

```php
Route::get('/pages/{slug}', [App\Http\Controllers\PageController::class, 'show'])
    ->name('pages.show');
```

Controller behavior (`PageController@show`):

- Finds page by `slug`.
- Requires `is_active = true`.
- Returns `firstOrFail()` (404 when not found/inactive).
- Renders Inertia page `Page` and passes the `page` record.

Inertia page resolution:

- In `resources/js/app.tsx`, Inertia resolves pages from:
  `./themes/default/pages/${name}.tsx`
- So `Inertia::render('Page', ...)` resolves to:
  `resources/js/themes/default/pages/Page.tsx`
  import React from 'react';
import { Head } from '@inertiajs/react';

interface PageProps {
    page: {
        title: string;
        content: string;
    };
}

import MainLayout from '@/themes/default/layouts/MainLayout';
import { useTranslation } from 'react-i18next';

export default function Page({ page }: PageProps) {
    const { t, i18n } = useTranslation();
    const title = page.title;
    const content = page.title;

    return (
        <MainLayout>
            <Head title={title} />

            {/* Hero Section with Gradient */}
            <div className="relative overflow-hidden bg-gradient-to-br from-primary/5 via-background to-background border-b">
                {/* Decorative Elements */}
                <div className="absolute inset-0 bg-grid-pattern opacity-[0.02]" />
                <div className="absolute top-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
                <div className="absolute bottom-0 left-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2" />

                <div className="container relative mx-auto px-4 py-16 md:py-24">
                    <div className="max-w-4xl mx-auto text-center">
                        <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight mb-4">
                            {title}
                        </h1>
                        <div className="w-20 h-1 bg-gradient-to-r from-primary/50 via-primary to-primary/50 mx-auto rounded-full" />
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="container mx-auto px-4 py-12 md:py-16">
                <div className="max-w-4xl mx-auto">
                    {/* Content Card */}
                    <article className="bg-card rounded-2xl shadow-sm border overflow-hidden transition-shadow hover:shadow-md">
                        {/* Content Body */}
                        <div className="p-6 md:p-10 lg:p-12">
                            <div
                                className="prose prose-slate dark:prose-invert max-w-none
                                    prose-headings:font-bold prose-headings:tracking-tight
                                    prose-h2:text-3xl prose-h2:mt-12 prose-h2:mb-6 prose-h2:pb-3 prose-h2:border-b prose-h2:border-border
                                    prose-h3:text-2xl prose-h3:mt-8 prose-h3:mb-4
                                    prose-h4:text-xl prose-h4:mt-6 prose-h4:mb-3
                                    prose-p:text-base prose-p:leading-relaxed prose-p:mb-4
                                    prose-a:text-primary prose-a:no-underline prose-a:font-medium hover:prose-a:underline
                                    prose-strong:text-foreground prose-strong:font-semibold
                                    prose-ul:my-6 prose-ul:space-y-2
                                    prose-ol:my-6 prose-ol:space-y-2
                                    prose-li:text-base prose-li:leading-relaxed
                                    prose-blockquote:border-l-4 prose-blockquote:border-primary/30 prose-blockquote:pl-6 prose-blockquote:italic prose-blockquote:text-muted-foreground
                                    prose-code:text-sm prose-code:bg-muted prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:font-mono prose-code:before:content-none prose-code:after:content-none
                                    prose-pre:bg-muted prose-pre:border prose-pre:border-border
                                    prose-img:rounded-lg prose-img:shadow-md
                                    prose-hr:border-border prose-hr:my-8"
                                dangerouslySetInnerHTML={{ __html: content }}
                            />
                        </div>

                        {/* Bottom Accent */}
                        <div className="h-1 bg-gradient-to-r from-transparent via-primary/20 to-transparent" />
                    </article>
                </div>
            </div>

            {/* Background Grid Pattern CSS (inline for simplicity) */}
            <style>{`
                .bg-grid-pattern {
                    background-image:
                        linear-gradient(to right, currentColor 1px, transparent 1px),
                        linear-gradient(to bottom, currentColor 1px, transparent 1px);
                    background-size: 4rem 4rem;
                }
            `}</style>
        </MainLayout>
    );
}


## Seeder: PageSeeder

`database/seeders/PageSeeder.php` seeds a predefined set of standard static pages such as:

- About Us
- Privacy Policy
- Return Policy
- Replacement Policy
- Delivery Policy
- Shipping Policy
- Terms of Service
- Contact Us

Seeding logic:

- Iterates over a pages array.
- Uses `Page::firstOrCreate(['slug' => $page['slug']], $page)`.
- This makes the seeder idempotent by `slug` (re-running will not duplicate rows with the same slug).

## Running the Seeder

You can seed page records directly with Artisan:

```bash
php artisan db:seed --class=PageSeeder
```

For tenant databases:

```bash
php artisan tenants:seed --class=PageSeeder
```

## Important Note

`DatabaseSeeder.php` currently does **not** call `PageSeeder` by default, so run `PageSeeder` explicitly or add it to your seeding flow.
