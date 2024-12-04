# ShaneBow CMS: `Cats`

Implements a dynamic hierarchical tree of categories for sites
 that use the *ShaneBow CMS* and *SiteBuilder*..

## Usage

This repo should be placed in `_lib/cms/cats` for the usual configuration
 where `_lib` is the library path specified in the *SiteBuilder* settings.

Then the following setup should be done in the site's `_content` directory:

*  `/application/views/admin/db-seeder.php.content` add line `~~cms/cats/ui/admin/seed-cats.div`
*  `/application/views/admin/cms-cats-manager.php.content`*  add line `~~cms/cats/ui/admin/cat-manager.view`
*  `/application/controllers.dir`: add line `~~cms/cats/controllers/Cat.php`
*  `/application/models.dir`: add line `~~cms/cats/models/Mcats.php`
*  `/application/config/form_validation.php`: add line `~~cms/cats/config/form_validation.php.div`
 
### Un-Publishing a Category

Categories are published (i.e. visible to all) by default.

Currently, the only way to unpublish a category is to:

* assign a *display page* to it, by setting the category's `id_page`
* unpublish the assigned page

## Migrations

ADD COLUMN
id_page int(10) unsigned not null default 0 AFTER id
