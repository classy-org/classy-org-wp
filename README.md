# Classy Wordpress Plugin for API version 2

Wordpress plugin for adding common embedded elements for Classy clients in their Wordpress sites.

## Installation

- Download this plugin
- Copy files to the `wp-content/plugins` directory of your Wordpress installation
- In your Wordpress admin dashboard, activate the 'Classy.org' plugin

## Usage

The plugin provides two ways to embed features into your Wordpress site: short codes and widgets.

NOTE:

> Data for embeddable widgets is cached for ten minutes

### Short codes

Embed a campaign progress bar for campaign #12345 with a title of "Campaign Progress Bar"

```
[classy-campaign-progress id="12345" title="Campaign Progress Bar"]
```

Embed a campaign overview for campaign #12345 with a title of "Campaign Overview"

```
[classy-campaign-overview id="12345" title="Campaign Overview"]
```

### Widgets

In the `Appearance > Widgets` section of Wordpress admin dashboard you'll find two new
widgets that you can drop into your widget areas.

- Classy.org: Campaign Progress Bar
- Classy.org: Campaign Overview

Each of these accept parameters for `id` and `title`, as above with the short codes.

## Roadmap

Additional embeddable items coming soon:

- Campaign top fundraiser leaderboard
- Campaign top fundraising team leaderboard
- Organization overview of campaigns

Other items:

- Configurable cache

## Contributing

When submitting a pull request, please make sure you've written good commit messages that include references to issues 
and clearly describe what the commit achieves. Use the commit body to explain what you did and why you did it. Thanks!

