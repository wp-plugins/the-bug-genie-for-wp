=== The Bug Genie for WP ===
Contributors: akerbos87
Tags: bugs, development, bug tracker
Requires at least: 3.2.0
Tested up to: 3.3.1
Stable tag: 1.0.1

Present issues from The Bug Genie in your Wordpress blog.

== Description ==

This plugin provides shortcodes that interface with the API of [The Bug Genie](http://www.thebuggenie.com), a great open-source bug tracking software. Currently, it is possible to link to individual issues, list issues by project and link to a project's bug report form.

These shortcodes are available:

* `[issue project=key issue=nr]` -- prints a short link to the specified issue. If you add `title=yes` the issue title is printed, too.
* `[issues project=key]` -- prints a list of all issues of the specified project. See the [FAQ](http://wordpress.org/extend/plugins/the-bug-genie-for-wp/faq) for filtering options.
* `[reportbug project=key]` -- Prints a link the the specified project's report form. You can provide a custom link text as shortcode content, i.e. `[reportbug project=key]My Text[/reportbug]`.

For a list of known issues see [here](http://bugs.verrech.net/thebuggenie/tbg4wp/issues/open).

== Installation ==

1. Install *The Bug Genie for WP* via your blog's plugin administration
2. Activate the plugin through the 'Plugins' menu in *WordPress*
3. Configure *The Bug Genie for WP* via the 'Settings' menu in *WordPress*

== Frequently Asked Questions ==

= What is the correct TBG address to enter? =
As TBG currently enforces you to have `/thebuggenie/` in your tracker address no matter what, we add it automatically. So just enter everything before that part. For example, this plugin's bugtracker is located at `http://bugs.verrech.net/thebuggenie/tbg4wp` so you would enter `http://bugs.verrech.net`.

= What to put as project/issue key? =
The project key you chose when creating the project. If you do not administrate TBG yourself, have at look at the URLs it uses. For instance, the project key is the last bit of your project's dashboard URL.

= What parameters does `issues` understand? =
If only we knew for sure! TBG's API is in a fetal state and not properly documented yet. You can download JSON yourself (append `/format/json` to a ticket's URL, for example) and try out some combinations. Be warned: it's quite verbose and badly formatted. They are working on it, but this is how it is today. Using the CLI client `tbg_cli` might be worthwhile, too.

Here is what we have figured out so far:

* `state=(open|closed|all)`
* `assigned_to=(username|none|all)`
* `issuetype=("Bug report"|"Feature request"|Enhancement|Task|"User story"|Idea)` -- or whatever types you have defined. Run `./tbg_cli help remote:list_issues` in your TBG root directory to find out more.

= How do I style the plugin's output? =
We use a bunch of (hopefully distinctly named) classes on the elements we generate so you can style them using CSS. We recommend you use a [child theme](http://codex.wordpress.org/Child_Themes) for that and similar theme extensions.

Fixed classes we use are:

* `debugbox` -- for `div` elements that contain debug messages (only shown if `WP_DEBUG` is enabled)
* `errorbox` -- for `div` elements that contain error messages
* `issue` -- for `span` elements created by `[issue]` and `[issues]`
* `closed` -- for `span` elements corresponding to a closed issue
* `open` -- for `span` elements corresponding to an open issue
* `issuelist` -- for `ul` elements created by `[issues]`
* `noissuelist` -- for `p` elements created by `[issues]` if there are no issues to list
* `reportbug` -- for `a` elements created by `[reportbug]`

Also, we insert a class to above mentioned `span` elements based on the respective issue's status. Using the default values of TBG you will get classes like `new`, `confirmed`, `beingworkedon` or `readyfortestingqa`. If in doubt, check the generated HTML.

= How can I help? =
You can

* use *The Bug Genie for WP*,
* vote on the *Wordpress* plugin portal for it,
* report bugs and/or propose ideas for improvement [here](http://bugs.verrech.net/thebuggenie/tbg4wp/issues/new) and
* blog about your experience with it.

== Changelog ==

= 1.0.1 =
Fixes a bug of settings page creation that causes incompatibility with another plugin by the same author.

= 1.0 =
Initial Release

== Upgrade Notice ==

= 1.0.1 =
Fixes a compatibility bug.
