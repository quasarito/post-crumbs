<?php
/*
Plugin Name: Post Crumbs
Plugin URI:  https://wordpress.org/plugins/post-crumbs
Description: Creates a signature (crumb) for your posts that can be used to track copied/stolen content. See Tools menu for configuration options.
Version:     0.180121
Author:      Huy Duong
Author URI:  https://github.com/quasarito/post-crumbs
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

define("HHDPC_HVER", "1"); // hash construction version
define("HHDPC_SHORTCODE_NAME", "pcrumb");
define("HHDPC_DEFAULT_HASH_ALGO", "sha1");
define("HHDPC_OPTION_HASH_ALGO", "hhdpc_hash_algo"); // key for hash algo in options table
define("HHDPC_OPTION_HASH_SALT", "hhdpc_hash_salt"); // key for hash salt in options table
define("HHDPC_OPTION_SALT_SIZE", "hhdpc_salt_size"); // key for salt size in options table
define("HHDPC_DEFAULT_SALT_SIZE", 1);
define("HHDPC_MIN_SALT_SIZE", 1);
define("HHDPC_MAX_SALT_SIZE", 10);
define("HHDPC_HASH_CRUMB", "hhdpc_hash_crumb"); // key for the calculated hash for a post in postmeta table
define("HHDPC_OPTION_AUTO_APPEND", "hhdpc_auto_append"); // key for auto-append crumb in options table
define("HHDPC_OPTION_AUTO_APPEND_TAG", "hhdpc_auto_append_tag"); // key for tag of auto-append crumb in options table
define("HHDPC_OPTION_AUTO_APPEND_CLASS", "hhdpc_auto_append_class"); // key for class attribute of auto-append crumb in options table


// Administration menu
define("HHDPC_SETTINGS", "hhdpc");

/* Adds a Post Crumb submenu to the Tools menu. */
function hhdpc_options_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    add_submenu_page(
        'tools.php',
        'Post Crumbs',
        'Post Crumbs',
        'manage_options',
        HHDPC_SETTINGS,
        'hhdpc_options_html'
    );
}
add_action('admin_menu', 'hhdpc_options_page');

/* The settings page */
function hhdpc_options_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Post Crumbs</h1>
        <p>
            The Post Crumb plugin will generate a unique signature (crumb) for every post which can be embedded
            within the post. This crumb can be used to determine whether your posts have been copied/stolen on the
            web by searching for the crumb on a search engine.
        </p><p>
            Using a crumb with a <i>salt</i> would increase confidence that any copied content was sourced
            from your post. Disabling the salt would lower that confidence level.
        </p>
        <h2>Using the shortcode</h2>
        <p>
            You can selectively choose which post and/or the location within a post a crumb appear by using the
            shortcode. You can embed the shortcode <code>[pcrumb]</code> anywhere in your post. A good place would
            be at the very end of your post. This will insert the crumb at the location of the shortcode.
            If you want to surround the crumb in an html tag (like <code>&lt;p&gt;</code> or <code>&lt;span&gt;</code>),
            you can specify the optional <code>tag</code> attribute with the html element name. There is also
            an optional <code>class</code> attribute for CSS formatting, if applicable.
            Eg: <b>[pcrumb tag="p" class="smaller"]</b>
        </p>
        <h2>Enabling Auto-append</h2>
        <p>
            Alternatively, you can have all posts (new and existing) contain a crumb by enabling <i>auto-append</i>.
            Turning on this option will insert the crumb at the end of every post. There is no need to include a
            shortcode in your post if you enable <i>auto-append</i>. Optionally, you can have the
            crumb surrounded with an html tag by specifying a <b>crumb tag</b>. Use the <b>crumb class</b> to include
            a <code>class</code> attribute with the html tag.
        </p>
        <?=settings_errors()?>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered settings 
            settings_fields(HHDPC_SETTINGS);
            // output setting sections and their fields
            do_settings_sections(HHDPC_SETTINGS);
            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php    
}

/* Outputs the form element for the hash digest dropdown */
function hhdpc_input_hash_algo_render($args)
{
    $algo_selected = get_option(HHDPC_OPTION_HASH_ALGO, HHDPC_DEFAULT_HASH_ALGO);
    ?><?=$algo?>
    <select name="<?=HHDPC_OPTION_HASH_ALGO?>">
        <?php foreach (hhdpc_allowed_hash_algo() as $algo) { ?>
        <option value="<?=$algo?>" <?=selected($algo_selected, $algo, false)?>><?=$algo?></option>
        <?php } ?>
    </select>
    <?php
}

/* Outputs the form elements for the salt */
function hhdpc_input_salt_size_render($args)
{
    $salt_size = get_option(HHDPC_OPTION_SALT_SIZE, HHDPC_DEFAULT_SALT_SIZE);
    $salt_value = get_option(HHDPC_OPTION_HASH_SALT, "");

    echo $salt_size > 0 ? "Value: <i>$salt_value</i>" : "<i>Disabled</i>";
    ?>
    <br/>
    <label>Level:
    <input name="<?=HHDPC_OPTION_SALT_SIZE?>" type="text" size="3" maxlength="3" value="<?=$salt_size?>" /></label>
    (between <?=HHDPC_MIN_SALT_SIZE?>-<?=HHDPC_MAX_SALT_SIZE?>, or 0 to disable)
    <br/>
    <label><input name="generate_salt" type="checkbox" value="true" /> Generate new salt</label>
    <?php
}

/* Outputs the form elements for the auto-append options */
function hhdpc_input_auto_append_render($args)
{
    $appendEnabled = get_option(HHDPC_OPTION_AUTO_APPEND, "false");
    ?>
    <input name="<?=HHDPC_OPTION_AUTO_APPEND?>" type="checkbox" value="true" <?=checked($appendEnabled, "true", false)?>/> Add crumb to the end of all posts automatically
    <?php
}

function hhdpc_input_auto_append_tag_render($args)
{
    $appendTag = get_option(HHDPC_OPTION_AUTO_APPEND_TAG, "");
    ?>
    <input name="<?=HHDPC_OPTION_AUTO_APPEND_TAG?>" type="text" value="<?=$appendTag?>" /> <i>Optional</i>
    <br/>
    Specify an html tag name to surround the crumb (like <code>p</code> or <code>span</code>).
    <?php
}

function hhdpc_input_auto_append_class_render($args)
{
    $appendClass = get_option(HHDPC_OPTION_AUTO_APPEND_CLASS, "");
    ?>
    <input name="<?=HHDPC_OPTION_AUTO_APPEND_CLASS?>" type="text" value="<?=$appendClass?>" /> <i>Optional</i>
    <br/>
    Specify an optional <code>class</code> attribute value with the tag for CSS formatting, if applicable.
    <?php
}

function hhdpc_settings_init()
{
    // Configuration options:
    // the hash digest dropdown
    register_setting(HHDPC_SETTINGS, HHDPC_OPTION_HASH_ALGO, "hhdpc_validate_hash_algo");
    // the salt size
    register_setting(HHDPC_SETTINGS, HHDPC_OPTION_SALT_SIZE, "hhdpc_validate_update_salt");
    // enable auto-append crumb
    register_setting(HHDPC_SETTINGS, HHDPC_OPTION_AUTO_APPEND, "hhdpc_validate_auto_append");
    // enable auto-append crumb tag
    register_setting(HHDPC_SETTINGS, HHDPC_OPTION_AUTO_APPEND_TAG, "hhdpc_validate_auto_append_tag");
    // enable auto-append crumb class
    register_setting(HHDPC_SETTINGS, HHDPC_OPTION_AUTO_APPEND_CLASS);


    // just a default section
    add_settings_section(
        'default',
        'Settings',
        '',
        HHDPC_SETTINGS
    );
 
    // add the fields
    add_settings_field(
        HHDPC_OPTION_HASH_ALGO,
        'Hash digest',
        'hhdpc_input_hash_algo_render',
        HHDPC_SETTINGS
    );
    add_settings_field(
        HHDPC_OPTION_SALT_SIZE,
        'Salt',
        'hhdpc_input_salt_size_render',
        HHDPC_SETTINGS
    );
    add_settings_field(
        HHDPC_OPTION_AUTO_APPEND,
        'Enable Auto-append Crumb',
        'hhdpc_input_auto_append_render',
        HHDPC_SETTINGS
    );
    add_settings_field(
        HHDPC_OPTION_AUTO_APPEND_TAG,
        'Auto-append Crumb Tag',
        'hhdpc_input_auto_append_tag_render',
        HHDPC_SETTINGS
    );
    add_settings_field(
        HHDPC_OPTION_AUTO_APPEND_CLASS,
        'Auto-append Crumb Class',
        'hhdpc_input_auto_append_class_render',
        HHDPC_SETTINGS
    );
}
add_action('admin_init', 'hhdpc_settings_init');

// ACTION
function hhdpc_activate()
{
    // generate a random salt for hashing by default, if it does not already exist
    $salt = base64_encode(random_bytes(hhdpc_salt_size_bytes(HHDPC_DEFAULT_SALT_SIZE)));

    if (empty(get_option(HHDPC_OPTION_HASH_ALGO)))
    {
        update_option(HHDPC_OPTION_HASH_ALGO, HHDPC_DEFAULT_HASH_ALGO);
        update_option(HHDPC_OPTION_SALT_SIZE, HHDPC_DEFAULT_SALT_SIZE);
        update_option(HHDPC_OPTION_HASH_SALT, $salt);
    }
}
register_activation_hook(__FILE__, "hhdpc_activate");

/* The salt size translates to a number of bytes by multiplying it by 3 and adding it to 30.
 * Because base64 encoding aligns at 3-byte lengths, we multiply by 3 to get no padding.
 * The baseline 30 was chosen arbitrarily to give a reasonable salt length at the minimum size.
 */
function hhdpc_salt_size_bytes($size)
{
    return ($size * 3) + 30;
}

/* Nothing to do when plugin deactivated */
// function hhdpc_deactivate()
// {
// }
// register_deactivation_hook(__FILE__, "hhdpc_deactivate");

function hhdpc_uninstall()
{
    delete_option(HHDPC_OPTION_HASH_ALGO);
    delete_option(HHDPC_OPTION_SALT_SIZE);
    delete_option(HHDPC_OPTION_HASH_SALT);
}
register_uninstall_hook(__FILE__, "hhdpc_uninstall");

function hhdpc_hash_post($postid)
{
    $post = get_post($postid);
    if (empty($post) || empty($post->post_content))
    {
        return; // nothing to do
    }

    $content = $post->post_content;
    $content = hhdpc_normalize_content($content);

    $algo = get_option(HHDPC_OPTION_HASH_ALGO, HHDPC_DEFAULT_HASH_ALGO);
    $salt = get_option(HHDPC_OPTION_HASH_SALT, "");
    $digest = hhdpc_calculate_hash($content, $salt);

    // the stored crumb metadata consists of an array with the elements:
    //  1: the signature
    //  2: the salt used
    //  3: the crumb version to determine how it was calculated
    //  4: the content used to calculate the crumb
    $meta_value = [ $digest, $salt, HHDPC_HVER, $content ];
    update_post_meta($postid, HHDPC_HASH_CRUMB, $meta_value);

    return $meta_value;
}
add_action("save_post", "hhdpc_hash_post");

/* Transforms the content to a suitable form for hashing which is agnostic to some changes.
 * It takes the post content and removes all punctuations and non-alphanumeric characters.
 */
function hhdpc_normalize_content($content)
{
    // Remove all characters that are not alphanueric and lowercase entire content
    return strtolower(preg_replace("/\\W+/", "", $content));
}

/* Calculates the crumb by taking the normalized content, then appending a salt if enabled,
 * and using the configured hash digest to get a value. The value is Base64-encoded.
 */
function hhdpc_calculate_hash($content, $salt)
{
    $algo = get_option(HHDPC_OPTION_HASH_ALGO, HHDPC_DEFAULT_HASH_ALGO);

    if (empty($salt)) {
        return base64_encode(hash($algo, $content, true));
    }
    else
    {
        return base64_encode(hash($algo, $content . $salt, true));
    }
}

// SHORTCODES
function hhdpc_pcrumb_shortcode($attrs = [], $content = null)
{
    $atts = shortcode_atts([
        "tag" => "",
        "class" => ""
    ], $attrs, HHDPC_SHORTCODE_NAME);

    // sanitize attributes
    $tag = ctype_alnum($atts["tag"]) ? $atts["tags"] : "";
    $class = htmlentities($atts["class"]);

    $post = get_post();
    $hash_meta = get_post_meta($post->ID, HHDPC_HASH_CRUMB, true);

    if (empty($hash_meta))
    {
        return "";
    }
    else if ($tag)
    {
        if ($class)
        {
            return "<$tag class=\"$class\">" . $hash_meta[0] . "</$tag>";            
        }
        else
        {
            return "<$tag>" . $hash_meta[0] . "</$tag>";
        }
    }
    else
    {
        return $hash_meta[0];
    }
}

function hhdpc_shortcode_init()
{
    add_shortcode(HHDPC_SHORTCODE_NAME, "hhdpc_pcrumb_shortcode");
}
add_action("init", "hhdpc_shortcode_init");

// add a filter when displaying post if option to auto-append crumb to the end
// of all posts is enabled
function hhdpc_auto_append_crumb($content)
{
    if (is_home())
    {
        // do not display hash on blog posts index page
        return $content;
    }

    $post = get_post();
    $hash_meta = get_post_meta($post->ID, HHDPC_HASH_CRUMB, true);

    if (empty($hash_meta))
    {
        // hash not calculated, so do it now...
        $hash_meta = hhdpc_hash_post($post->ID);
    }

    $tag = get_option(HHDPC_OPTION_AUTO_APPEND_TAG, "");
    $tag = ctype_alnum($tag) ? $tag : ""; // sanitize
    if ($tag)
    {
        $class = get_option(HHDPC_OPTION_AUTO_APPEND_CLASS, "");
        $class = htmlentities($class); // sanitize
        if ($class)
        {
            return $content . "<$tag class=\"$class\">" . $hash_meta[0] . "</$tag>";            
        }
        else
        {
            return $content . "<$tag>" . $hash_meta[0] . "</$tag>";
        }
    }
    else
    {
        return $content . $hash_meta[0];
    }
}
if (get_option(HHDPC_OPTION_AUTO_APPEND, false) == "true")
{
    add_filter('the_content', 'hhdpc_auto_append_crumb');
}

function hhdpc_allowed_hash_algo()
{
    $algos_allowed = ['md5', 'sha1', 'sha256'];
    $algos_avail = hash_algos();
    
    $result = [];
    foreach ($algos_allowed as $algo)
    {
        if (in_array($algo, $algos_avail))
        {
            array_push($result, $algo);
        }
        else
        {
            error_log("Hash digest '$algo' not available.");
        }
    }

    return $result;
}

/* Sanitization of hash digest settings. Only allowed digests are permitted.
 * If any other value is found, it will it to the default one.
 */
function hhdpc_validate_hash_algo($algo)
{
    if (in_array($algo, hhdpc_allowed_hash_algo()))
    {
        return $algo;
    }
    else
    {
        add_settings_error(HHDPC_OPTION_HASH_ALGO, HHDPC_OPTION_HASH_ALGO,
            "$algo is not supported. Using default: " . HHDPC_DEFAULT_HASH_ALGO);
        return HHDPC_DEFAULT_HASH_ALGO;
    }
}

function hhdpc_validate_update_salt($size)
{
    $salt_size = hhdpc_validate_salt_size($size);

    // if salt size has changed or the checkbox has been checked, generate new salt.
    $old_salt_size = get_option(HHDPC_OPTION_SALT_SIZE);
    if ($salt_size != $old_salt_size || $_POST["generate_salt"] == "true")
    {
        $salt = $salt_size == 0 ? "" : base64_encode(random_bytes(hhdpc_salt_size_bytes($salt_size)));
        update_option(HHDPC_OPTION_HASH_SALT, $salt);

        add_settings_error(HHDPC_OPTION_SALT_SIZE, HHDPC_OPTION_SALT_SIZE,
            "Salt value changed from $old_salt_size to $salt_size.", "updated");
    }

    return $salt_size;
}

/* Sanitization of salt size setting. Invalid values will have the size set to the default.
 * Values exceeding the maximum allowed will set the size to the maximum value. A zero value
 * disables using a salt.
 */
function hhdpc_validate_salt_size($size)
{
    if (is_numeric($size))
    {
        $salt_size = intval($size);
        if ($size < HHDPC_MIN_SALT_SIZE && $size != 0) {
            add_settings_error(HHDPC_OPTION_SALT_SIZE, HHDPC_OPTION_SALT_SIZE,
                "Invalid salt size. Using default: " . HHDPC_DEFAULT_SALT_SIZE);
            return HHDPC_DEFAULT_SALT_SIZE;
        }
        else if ($size > HHDPC_MAX_SALT_SIZE)
        {
            add_settings_error(HHDPC_OPTION_SALT_SIZE, HHDPC_OPTION_SALT_SIZE,
                "Salt size too large. Using maximum allowed: " . HHDPC_MAX_SALT_SIZE);
            return HHDPC_MAX_SALT_SIZE;
        }
        else
        {
            return $salt_size;
        }
    }
    else
    {
        add_settings_error(HHDPC_OPTION_SALT_SIZE, HHDPC_OPTION_SALT_SIZE,
            "Invalid salt size. Using default: " . HHDPC_DEFAULT_SALT_SIZE);
        return HHDPC_DEFAULT_SALT_SIZE;
    }
}

function hhdpc_validate_auto_append($enabled)
{
    return ($enabled == "true") ? "true" : "false";
}

function hhdpc_validate_auto_append_tag($tag)
{
    if (empty($tag) || ctype_alnum($tag))
    {
        return $tag;
    }
    else
    {
        add_settings_error(HHDPC_OPTION_AUTO_APPEND_TAG, HHDPC_OPTION_AUTO_APPEND_TAG,
            "Invalid auto-append tag name: " . $tag);
        return "";
    }
}
?>