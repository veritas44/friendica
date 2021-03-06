Friendica Addon development
==============

* [Home](help)

Please see the sample addon 'randplace' for a working example of using some of these features.
Addons work by intercepting event hooks - which must be registered.
Modules work by intercepting specific page requests (by URL path).

Addon names cannot contain spaces or other punctuation and are used as filenames and function names.
You may supply a "friendly" name within the comment block.
Each addon must contain both an install and an uninstall function based on the addon name.
For instance "addon1name_install()".
These two functions take no arguments and are usually responsible for registering (and unregistering) event hooks that your addon will require.
The install and uninstall functions will also be called (i.e. re-installed) if the addon changes after installation.
Therefore your uninstall should not destroy data and install should consider that data may already exist.
Future extensions may provide for "setup" amd "remove".

Addons should contain a comment block with the four following parameters:

    /*
     * Name: My Great Addon
     * Description: This is what my addon does. It's really cool.
     * Version: 1.0
     * Author: John Q. Public <john@myfriendicasite.com>
     */

Register your addon hooks during installation.

    Addon::registerHook($hookname, $file, $function);

$hookname is a string and corresponds to a known Friendica hook.

$file is a pathname relative to the top-level Friendica directory.
This *should* be 'addon/addon_name/addon_name.php' in most cases.

$function is a string and is the name of the function which will be executed when the hook is called.

Please also add a README or README.md file to the addon directory.
It will be displayed in the admin panel and should include some further information in addition to the header information.

Arguments
---
Your hook callback functions will be called with at least one and possibly two arguments

    function myhook_function(App $a, &$b) {

    }


If you wish to make changes to the calling data, you must declare them as reference variables (with '&') during function declaration.

#### $a
$a is the Friendica 'App' class.
It contains a wealth of information about the current state of Friendica:

* which module has been called,
* configuration information,
* the page contents at the point the hook was invoked,
* profile and user information, etc.

It is recommeded you call this '$a' to match its usage elsewhere.

#### $b
$b can be called anything you like.
This is information specific to the hook currently being processed, and generally contains information that is being immediately processed or acted on that you can use, display, or alter.
Remember to declare it with '&' if you wish to alter it.

Modules
---

Addons may also act as "modules" and intercept all page requests for a given URL path.
In order for a addon to act as a module it needs to define a function "addon_name_module()" which takes no arguments and needs not do anything.

If this function exists, you will now receive all page requests for "http://my.web.site/addon_name" - with any number of URL components as additional arguments.
These are parsed into an array $a->argv, with a corresponding $a->argc indicating the number of URL components.
So http://my.web.site/addon/arg1/arg2 would look for a module named "addon" and pass its module functions the $a App structure (which is available to many components).
This will include:

    $a->argc = 3
    $a->argv = array(0 => 'addon', 1 => 'arg1', 2 => 'arg2');

Your module functions will often contain the function addon_name_content(App $a), which defines and returns the page body content.
They may also contain addon_name_post(App $a) which is called before the _content function and typically handles the results of POST forms.
You may also have addon_name_init(App $a) which is called very early on and often does module initialisation.

Templates
---

If your addon needs some template, you can use the Friendica template system.
Friendica uses [smarty3](http://www.smarty.net/) as a template engine.

Put your tpl files in the *templates/* subfolder of your addon.

In your code, like in the function addon_name_content(), load the template file and execute it passing needed values:

    # load template file. first argument is the template name,
    # second is the addon path relative to friendica top folder
    $tpl = get_markup_template('mytemplate.tpl', 'addon/addon_name/');

    # apply template. first argument is the loaded template,
    # second an array of 'name'=>'values' to pass to template
    $output = replace_macros($tpl,array(
        'title' => 'My beautiful addon',
    ));

See also the wiki page [Quick Template Guide](https://github.com/friendica/friendica/wiki/Quick-Template-Guide).

Current hooks
-------------

### 'authenticate'
'authenticate' is called when a user attempts to login.
$b is an array containing:

    'username' => the supplied username
    'password' => the supplied password
    'authenticated' => set this to non-zero to authenticate the user.
    'user_record' => successful authentication must also return a valid user record from the database

### 'logged_in'
'logged_in' is called after a user has successfully logged in.
$b contains the $a->user array.

### 'display_item'
'display_item' is called when formatting a post for display.
$b is an array:

    'item' => The item (array) details pulled from the database
    'output' => the (string) HTML representation of this item prior to adding it to the page

### 'post_local'
* called when a status post or comment is entered on the local system
* $b is the item array of the information to be stored in the database
* Please note: body contents are bbcode - not HTML

### 'post_local_end'
* called when a local status post or comment has been stored on the local system
* $b is the item array of the information which has just been stored in the database
* Please note: body contents are bbcode - not HTML

### 'post_remote'
* called when receiving a post from another source. This may also be used to post local activity or system generated messages.
* $b is the item array of information to be stored in the database and the item body is bbcode.

### 'settings_form'
* called when generating the HTML for the user Settings page
* $b is the (string) HTML of the settings page before the final '</form>' tag.

### 'settings_post'
* called when the Settings pages are submitted
* $b is the $_POST array

### 'addon_settings'
* called when generating the HTML for the addon settings page
* $b is the (string) HTML of the addon settings page before the final '</form>' tag.

### 'addon_settings_post'
* called when the Addon Settings pages are submitted
* $b is the $_POST array

### 'profile_post'
* called when posting a profile page
* $b is the $_POST array

### 'profile_edit'
'profile_edit' is called prior to output of profile edit page.
$b is an array containing:

    'profile' => profile (array) record from the database
    'entry' => the (string) HTML of the generated entry

### 'profile_advanced'
* called when the HTML is generated for the 'Advanced profile', corresponding to the 'Profile' tab within a person's profile page
* $b is the (string) HTML representation of the generated profile
* The profile array details are in $a->profile.

### 'directory_item'
'directory_item' is called from the Directory page when formatting an item for display.
$b is an array:

    'contact' => contact (array) record for the person from the database
    'entry' => the (string) HTML of the generated entry

### 'profile_sidebar_enter'
* called prior to generating the sidebar "short" profile for a page
* $b is the person's profile array

### 'profile_sidebar'
'profile_sidebar is called when generating the sidebar "short" profile for a page.
$b is an array:

    'profile' => profile (array) record for the person from the database
    'entry' => the (string) HTML of the generated entry

### 'contact_block_end'
is called when formatting the block of contacts/friends on a profile sidebar has completed.
$b is an array:

    'contacts' => array of contacts
    'output' => the (string) generated HTML of the contact block

### 'bbcode'
* called during conversion of bbcode to html
* $b is a string converted text

### 'html2bbcode'
* called during conversion of html to bbcode (e.g. remote message posting)
* $b is a string converted text

### 'page_header'
* called after building the page navigation section
* $b is a string HTML of nav region

### 'personal_xrd'
'personal_xrd' is called prior to output of personal XRD file.
$b is an array:

    'user' => the user record for the person
    'xml' => the complete XML to be output

### 'home_content'
* called prior to output home page content, shown to unlogged users
* $b is (string) HTML of section region

### 'contact_edit'
is called when editing contact details on an individual from the Contacts page.
$b is an array:

    'contact' => contact record (array) of target contact
    'output' => the (string) generated HTML of the contact edit page

### 'contact_edit_post'
* called when posting the contact edit page.
* $b is the $_POST array

### 'init_1'
* called just after DB has been opened and before session start
* $b is not used or passed

### 'page_end'
* called after HTML content functions have completed
* $b is (string) HTML of content div

### 'avatar_lookup'
'avatar_lookup' is called when looking up the avatar.
$b is an array:

    'size' => the size of the avatar that will be looked up
    'email' => email to look up the avatar for
    'url' => the (string) generated URL of the avatar

### 'emailer_send_prepare'
'emailer_send_prepare' called from Emailer::send() before building the mime message.
$b is an array, params to Emailer::send()

    'fromName' => name of the sender
    'fromEmail' => email fo the sender
    'replyTo' => replyTo address to direct responses
    'toEmail' => destination email address
    'messageSubject' => subject of the message
    'htmlVersion' => html version of the message
    'textVersion' => text only version of the message
    'additionalMailHeader' => additions to the smtp mail header

### 'emailer_send'
is called before calling PHP's mail().
$b is an array, params to mail()

    'to'
    'subject'
    'body'
    'headers'

### 'nav_info'
is called after the navigational menu is build in include/nav.php.
$b is an array containing $nav from nav.php.

### 'template_vars'
is called before vars are passed to the template engine to render the page.
The registered function can add,change or remove variables passed to template.
$b is an array with:

    'template' => filename of template
    'vars' => array of vars passed to template

### 'acl_lookup_end'
is called after the other queries have passed.
The registered function can add, change or remove the acl_lookup() variables.

    'results' => array of the acl_lookup() vars

### 'prepare_body_init'
Called at the start of prepare_body
Hook data:
    'item' => item array (input/output)

### 'prepare_body_content_filter'
Called before the HTML conversion in prepare_body. If the item matches a content filter rule set by an addon, it should
just add the reason to the filter_reasons element of the hook data.
Hook data:
    'item' => item array (input)
    'filter_reasons' => reasons array (input/output)

### 'prepare_body'
Called after the HTML conversion in prepare_body.
Hook data:
    'item' => item array (input)
    'html' => converted item body (input/output)
    'is_preview' => post preview flag (input)
    'filter_reasons' => reasons array (input)

### 'prepare_body_final'
Called at the end of prepare_body.
Hook data:
    'item' => item array (input)
    'html' => converted item body (input/output)

Complete list of hook callbacks
---

Here is a complete list of all hook callbacks with file locations (as of 01-Apr-2018). Please see the source for details of any hooks not documented above.

### index.php

    Addon::callHooks('init_1');
    Addon::callHooks('app_menu', $arr);
    Addon::callHooks('page_content_top', $a->page['content']);
    Addon::callHooks($a->module.'_mod_init', $placeholder);
    Addon::callHooks($a->module.'_mod_init', $placeholder);
    Addon::callHooks($a->module.'_mod_post', $_POST);
    Addon::callHooks($a->module.'_mod_afterpost', $placeholder);
    Addon::callHooks($a->module.'_mod_content', $arr);
    Addon::callHooks($a->module.'_mod_aftercontent', $arr);
    Addon::callHooks('page_end', $a->page['content']);
    
### include/api.php

    Addon::callHooks('logged_in', $a->user);
    Addon::callHooks('authenticate', $addon_auth);
    Addon::callHooks('logged_in', $a->user);

### include/enotify.php
    
    Addon::callHooks('enotify', $h);
    Addon::callHooks('enotify_store', $datarray);
    Addon::callHooks('enotify_mail', $datarray);
    Addon::callHooks('check_item_notification', $notification_data);
    
### include/conversation.php

    Addon::callHooks('conversation_start', $cb);
    Addon::callHooks('render_location', $locate);
    Addon::callHooks('display_item', $arr);
    Addon::callHooks('display_item', $arr);
    Addon::callHooks('item_photo_menu', $args);
    Addon::callHooks('jot_tool', $jotplugins);

### include/security.php

    Addon::callHooks('logged_in', $a->user);

### include/text.php

    Addon::callHooks('contact_block_end', $arr);
    Addon::callHooks('poke_verbs', $arr);
    Addon::callHooks('prepare_body_init', $item);
    Addon::callHooks('prepare_body_content_filter', $hook_data);
    Addon::callHooks('prepare_body', $hook_data);
    Addon::callHooks('prepare_body_final', $hook_data);

### include/items.php

    Addon::callHooks('page_info_data', $data);

### mod/directory.php

    Addon::callHooks('directory_item', $arr);

### mod/xrd.php

    Addon::callHooks('personal_xrd', $arr);

### mod/ping.php

    Addon::callHooks('network_ping', $arr);

### mod/parse_url.php

    Addon::callHooks("parse_link", $arr);

### mod/manage.php

    Addon::callHooks('home_init', $ret);

### mod/acl.php

    Addon::callHooks('acl_lookup_end', $results);

### mod/network.php

    Addon::callHooks('network_content_init', $arr);
    Addon::callHooks('network_tabs', $arr);

### mod/friendica.php

    Addon::callHooks('about_hook', $o);
    
### mod/subthread.php

    Addon::callHooks('post_local_end', $arr);

### mod/profiles.php

    Addon::callHooks('profile_post', $_POST);
    Addon::callHooks('profile_edit', $arr);

### mod/settings.php

    Addon::callHooks('addon_settings_post', $_POST);
    Addon::callHooks('connector_settings_post', $_POST);
    Addon::callHooks('display_settings_post', $_POST);
    Addon::callHooks('settings_post', $_POST);
    Addon::callHooks('addon_settings', $settings_addons);
    Addon::callHooks('connector_settings', $settings_connectors);
    Addon::callHooks('display_settings', $o);
    Addon::callHooks('settings_form', $o);

### mod/photos.php

    Addon::callHooks('photo_post_init', $_POST);
    Addon::callHooks('photo_post_file', $ret);
    Addon::callHooks('photo_post_end', $foo);
    Addon::callHooks('photo_post_end', $foo);
    Addon::callHooks('photo_post_end', $foo);
    Addon::callHooks('photo_post_end', $foo);
    Addon::callHooks('photo_post_end', intval($item_id));
    Addon::callHooks('photo_upload_form', $ret);

### mod/profile.php

    Addon::callHooks('profile_advanced', $o);

### mod/home.php

    Addon::callHooks('home_init', $ret);
    Addon::callHooks("home_content", $content);

### mod/poke.php

    Addon::callHooks('post_local_end', $arr);

### mod/contacts.php

    Addon::callHooks('contact_edit_post', $_POST);
    Addon::callHooks('contact_edit', $arr);

### mod/tagger.php

    Addon::callHooks('post_local_end', $arr);

### mod/lockview.php

    Addon::callHooks('lockview_content', $item);

### mod/uexport.php

    Addon::callHooks('uexport_options', $options);

### mod/register.php

    Addon::callHooks('register_post', $arr);
    Addon::callHooks('register_form', $arr);

### mod/item.php

    Addon::callHooks('post_local_start', $_REQUEST);
    Addon::callHooks('post_local', $datarray);
    Addon::callHooks('post_local_end', $datarray);

### mod/editpost.php    

    Addon::callHooks('jot_tool', $jotplugins);

### src/Network/FKOAuth1.php

    Addon::callHooks('logged_in', $a->user);

### src/Render/FriendicaSmartyEngine.php

    Addon::callHooks("template_vars", $arr);

### src/Model/Item.php

    Addon::callHooks('post_local', $item);
    Addon::callHooks('post_remote', $item);
    Addon::callHooks('post_local_end', $posted_item);
    Addon::callHooks('post_remote_end', $posted_item);
    Addon::callHooks('tagged', $arr);
    Addon::callHooks('post_local_end', $new_item);

### src/Model/Contact.php

    Addon::callHooks('contact_photo_menu', $args);
    Addon::callHooks('follow', $arr);

### src/Model/Profile.php

    Addon::callHooks('profile_sidebar_enter', $profile);
    Addon::callHooks('profile_sidebar', $arr);
    Addon::callHooks('profile_tabs', $arr);
    Addon::callHooks('zrl_init', $arr);

### src/Model/Event.php

    Addon::callHooks('event_updated', $event['id']);
    Addon::callHooks("event_created", $event['id']);

### src/Model/User.php

    Addon::callHooks('register_account', $uid);
    Addon::callHooks('remove_user', $user);

### src/Content/Text/BBCode.php

    Addon::callHooks('bbcode', $text);
    Addon::callHooks('bb2diaspora', $text);

### src/Content/Text/HTML.php

    Addon::callHooks('html2bbcode', $message);

### src/Content/Smilies.php

    Addon::callHooks('smilie', $params);

### src/Content/Feature.php

    Addon::callHooks('isEnabled', $arr);
    Addon::callHooks('get', $arr);

### src/Content/ContactSelector.php

    Addon::callHooks('network_to_name', $nets);
    Addon::callHooks('gender_selector', $select);
    Addon::callHooks('sexpref_selector', $select);
    Addon::callHooks('marital_selector', $select);

### src/Content/OEmbed.php    

    Addon::callHooks('oembed_fetch_url', $embedurl, $j);

### src/Content/Nav.php    

    Addon::callHooks('page_header', $a->page['nav']);
    Addon::callHooks('nav_info', $nav);

### src/Worker/Directory.php

    Addon::callHooks('globaldir_update', $arr);

### src/Worker/Notifier.php

    Addon::callHooks('notifier_end', $target_item);

### src/Worker/Queue.php    

    Addon::callHooks('queue_predeliver', $r);
    Addon::callHooks('queue_deliver', $params);

### src/Module/Login.php

    Addon::callHooks('authenticate', $addon_auth);
    Addon::callHooks('login_hook', $o);

### src/Module/Logout.php    

    Addon::callHooks("logging_out");

### src/Object/Post.php

    Addon::callHooks('render_location', $locate);
    Addon::callHooks('display_item', $arr);

### src/Core/ACL.php

    Addon::callHooks('contact_select_options', $x);
    Addon::callHooks($a->module.'_pre_'.$selname, $arr);
    Addon::callHooks($a->module.'_post_'.$selname, $o);
    Addon::callHooks($a->module.'_pre_'.$selname, $arr);
    Addon::callHooks($a->module.'_post_'.$selname, $o);
    Addon::callHooks('jot_networks', $jotnets);

### src/Core/Worker.php

    Addon::callHooks("proc_run", $arr);

### src/Util/Emailer.php

    Addon::callHooks('emailer_send_prepare', $params);
    Addon::callHooks("emailer_send", $hookdata);

### src/Util/Map.php

    Addon::callHooks('generate_map', $arr);
    Addon::callHooks('generate_named_map', $arr);
    Addon::callHooks('Map::getCoordinates', $arr);

### src/Util/Network.php

    Addon::callHooks('avatar_lookup', $avatar);

### src/Util/ParseUrl.php

    Addon::callHooks("getsiteinfo", $siteinfo);

### src/Protocol/DFRN.php

    Addon::callHooks('atom_feed_end', $atom);
    Addon::callHooks('atom_feed_end', $atom);
