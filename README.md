# Magento2 Sitemap Warmer

If you have a sitemap, just run this command to warmup your pages.

## Install

```
composer require fruitcake/magento2-sitemap-warmer
php bin/magento setup:upgrade
```

## Usage

The cron does not run automatically. You can add it to you crontab manually.

Basic usage, use the sitemaps from Magento and execute 100 visits:
```
php bin/magento sitemap:warm
```

Specific sitemap:
```
php bin/magento sitemap:warm https://example.com/sitemap.xml
```

Specific sitemap:
```
php bin/magento sitemap:warm https://example.com/sitemap.xml
```

Only urls with priority 0.5, sleep 1 sec between each requests and execute 500 urls
```
php bin/magento sitemap:warm --priority=0.5 --sleep=1 --requests=500
```

I suggest you use flock in your crontab to avoid overlap.

## TODO

 - Add cron schedule/settings in admin
 - Run cron with Magento cron, avoid overlap