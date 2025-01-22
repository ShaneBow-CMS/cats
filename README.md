# ShaneBow CMS: `Cats`

Implements a dynamic hierarchical tree of categories for sites
 that use the *ShaneBow CMS* and *SiteBuilder*..

## Usage

This repo should be placed in `_lib/cms/cats` for the usual configuration
 where `_lib` is the library path specified in the *SiteBuilder* settings.

Then the following setup should be done in the site's `_content` directory:

* `/application/views/admin/db-seeder.php.content` add line `~~cms/cats/ui/admin/seed-cats.div`
* `/application/views/admin/cms-cats-manager.php.content`*  add line `~~cms/cats/ui/admin/cat-manager.view`
* `/application/controllers.dir`: add line `~~cms/cats/controllers/Cat.php`
* `/application/models.dir`: add line `~~cms/cats/models/Mcats.php`
* `/application/config/form_validation.php`: add line `~~cms/cats/config/form_validation.php.div`

### Category Display

To customize a category (highly recommended for SEO), create a *page* using
 the page editor, then assigned it to the __sub-category__ it should display
 by setting the category's `id_page` in the Cat manager.

Note: The newly created page must also set it's own *category* in the
 *Page Editor*: This page is the __parent category__.

The code does not yet verify that the __sub-category__ is a direct child
 of the __parent category__, but weirdness will ensue if this is not the
 case (but it shouldn't actually break anything).

Generally, somplace in the *content* section of the page there
 should be a `div` to hold the child pages:

~~~html
<div id="child-pages"></div>
~~~

And then in the *tail* section, we use this to populate it:

~~~javascript
<script>
 new UBOW.PageSummaryFetcher('#child-pages', {
  per_page:12,
  col_class: "col-xs-4, col-sm-3",
  extra: { by:'cat', id:UBOW.meta.kids },
  renderedPage: () => UBOW.resize(),
  });
</script>
~~~

### Un-Publishing a Category

Categories are published (i.e. visible to all) by default.

Currently, the only way to unpublish a category is to:

* assign a *display page* to it, by setting the category's `id_page`
* unpublish the assigned page

## Migrations

ADD COLUMN
id_page int(10) unsigned not null default 0 AFTER id

REMOVE COLUMNS
etc
content
