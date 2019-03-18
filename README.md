[![Build Status](https://travis-ci.org/blhylton/infores-store-locator-plugin.svg?branch=master)](https://travis-ci.org/blhylton/infores-store-locator-plugin)

# InfoRes Store Locator Scraper & Parser

This project was born out of a need to get store/product location data from an InfoRes site for a client. With no 
apparent API and a short time frame, it made sense to just scrape the page utilizing Guzzle and parse the information
directly from the HTML tables. Since the site in question is a WordPress site, it made the most sense to implement it as
a WordPress plugin.

## Installation

There are technically two different types of installation: Installing the plugin in WordPress, and installing the plugin
locally for development.

### Requirements

This plugin requires PHP ^7.2. It could potentially be adapted to work with 7.1, but would require changing some
dependencies to deprecated versions. If you are using PHP 5.x or 7.0, it is recommended that you upgrade **immediately**
as those versions are no longer being supported or patched.

### Installing in WordPress

You can find zip files containing all the releases in [the dist folder](https://github.com/blhylton/infores-store-locator-plugin/tree/master/dist)
of the repo's master branch. Download the zip file, and utilize the wordpress plugin screen to install from zip.

Once the plugin is installed and activated. There is a configuration screen under "Settings > InfoRes Store Locator Configuration"
that has some parameters that are required to be set. All request parameters are from the URL that goes to a search 
result for a given product. Searchable Post Types is set to create a custom meta box for including the product ID on a 
given post type.

### Installing Locally for Development

Use the [Composer](https://getcomposer.org/) package manager to install the dependencies.

```bash
composer install
```

## Usage

While this plugin is mostly meant to be used in conjunction with WordPress, it could be easily altered so that the logic
works in other environments. All the code that is not WordPress specific is in the `src` folder.

Once installed in WordPress, this plugin will expose two REST API endpoints `/blhirsl/v1/get-stores` and `/blhirsl/v1/posts`

### get-stores

Returns a list of stores that fit the given parameters.

#### Parameters

- `zipCode` (required) - Zip code to search from
- `productId` (required) - Product id to search for
- `distance` (optional) - Distance in miles to include in the search radius (defaults to 20)
- `pageNum` (optional) - Page to retrieve (defaults to 1)

#### Return

```json
{
  "data": [],
  "morePages": false,
  "currentPageNumber" : 1
}
```

`data` is an array of store objects with all the information that could be gleaned from the page. The store objects look
like:

```json
{
  "Store": "STORE NAME",
  "Address": "STORE ADDRESS",
  "Phone": "STORE PHONE NUMBER",
  "Distance": "DISTANCE TO STORE FROM ZIP CODE"
}
```

`morePages` is a boolean value indicating if there are more pages to fetch after this one. Due to the nature of the data
presentation we are scraping, we are limited to 10 stores per page.

`currentPageNumber` is an integer indicating which page you are on. This is useful in conjunction with `morePages` to 
traverse the overall list.

### posts

Returns a list of post titles that have a `productId` meta field set along with their corresponding Product Id.

#### Parameters

*None*

#### Return

```json
{
  "<productId>": "<productTitle>"
}
```

The object key is the Product ID in the metaData needed for your search. The object value is the Post Title for exposing
to your users.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)