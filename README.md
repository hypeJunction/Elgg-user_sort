User List Sorting for Elgg
==========================
![Elgg 2.0](https://img.shields.io/badge/Elgg-2.0.x-orange.svg?style=flat-square)

*** NO LONGER MAINTAINED ***
The functionality of this plugin has been moved to [hypeLists](https://github.com/hypeJunction/hypeLists)

## Features

 * Implements generic API and UI for sorting user lists
 * By default, provides sorting by Name, Friend count, Registration date, and Last seen
 * Extendable via hooks

![User Sort](https://raw.github.com/hypeJunction/Elgg-user_sort/master/screenshots/members.png "User Search and Sort Interface")

## Usage


### List users

```php

// display a sortable list of friends
$options = array(
	'relationship' => 'friend',
	'relationship_guid' => $user->guid,
);

echo elgg_view('lists/users', array(
	'options' => $options,
	'callback' => 'elgg_list_entities_from_relationship',
));
```

### Custom sort fields

Use `'sort_fields','user'` plugin hook to add new fields to the sort select input.
Use `'sort_options', 'user'` to add custom queries to ege* options.
