#InfoRes Store Locator Scraper & Parser

This project was born out of a need to get store/product location data from an InfoRes site for a client. With no 
apparent API and a short time frame, it made sense to just scrape the page utilizing Guzzle and parse the information
directly from the HTML tables. Since the site in question is a WordPress site, it made the most sense to implement it as
a WordPress plugin.

##Installation

There are technically two different types of installation: Installing the plugin in WordPress, and installing the plugin
locally for development.

###Installing in WordPress

*coming soon (Once I have a functional build and a build pipeline setup)*

###Installing Locally for Development

Use the [Composer](https://getcomposer.org/) package manager to install the dependencies.

```bash
composer install
```

##Usage

While this plugin is mostly meant to be used in conjunction with WordPress, it could be easily altered so that the logic
works in other environments. All the code that is not WordPress specific is in the `src` folder.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)