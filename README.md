# BuddyPress Profile Type Assigner

**Contributors:** garyj  
**Tags:** BuddyPress, BuddyBoss, profile types, roles  
**Requires at least:** 6.6  
**Tested up to:** 6.8  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Automatically assigns BuddyPress profile types and WordPress roles based on email domain.

## Description

This plugin automatically assigns BuddyPress profile types (e.g., Customer, Partner, Teacher, Student, etc.) and corresponding WordPress roles to users based on their email domain. It's particularly useful for:

Particularly useful for assigning profile types that aren't available for new users to pick from during registration.

The plugin integrates with BuddyPress/BuddyBoss's member type system and WordPress's role management to provide a seamless user onboarding experience.

## Features

* Automatic profile type assignment based on email domain.
* Configurable domain-to-profile-type mapping (code - no UI yet).
* Maintains role assignment based on BuddyPress member type configuration.

## Installation

1. Upload the `buddypress-profile-type-assigner` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the domain-to-profile-type mapping in the plugin settings.

## Configuration

The plugin uses a simple array-based configuration for mapping email domains to profile types. Define the following in a mu-plugin, theme functions file, or custom plugin:

~~~php
define(
    'BUDDYPRESS_PROFILE_TYPE_DOMAIN_MAPPING',
    array(
        '@example.com'  => 'profile-type-1',
        '@example.org'  => 'profile-type-2',
        '@example.net'  => 'profile-type-3',
        '@example.info' => 'profile-type-4',
    )
);
~~~

Multiple domains can be assigned to the same profile type.

You can find the profile type slug at the bottom of editing the profile type, but it's the lowercase, hyphenated version of the profile type name. e.g. _Junior Developer_ would have a slug of `junior-developer`.

## Requirements

* WordPress 6.6 or higher
* BuddyPress or BuddyBoss Platform
* Member types configured in BuddyPress/BuddyBoss
* WordPress roles configured for member types

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a complete list of changes.

## License

This plugin is licensed under the GPLv2 or later. See [LICENSE](LICENSE) for details.
