# ShaneBow CMS: `Cats`

Implements a dynamic hierarchical tree of categories for sites
 that use the *ShaneBow CMS* and *SiteBuilder*..

## Usage

This repo should be placed in `_lib/cms/cats` for the usual configuration
 where `_lib` is the library path specified in the *SiteBuilder* settings.

Then the following setup should be done in the site's `_content` directory:

*  `/application/views/admin/db-seeder.php.content` add line `~~cms/cats/views/admin/seed-cats.div`
*  `/application/views/admin/cat-tree.php.content`*  add line `~~cms/cats/views/admin/cat-tree.view`
*  `/application/controllers.dir`: add line `~~cms/cats/controllers/Cat.php`
*  `/application/models.dir`: add line `~~cms/cats/models/Mcats.php`
*  `/application/config/form_validation.php`: add line `~~cms/cats/config/form_validation.php.div`
 