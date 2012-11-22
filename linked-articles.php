<?php
/**
 * @package linked_articles
 * @version 1.0
 */
/*
Plugin Name: Linked Articles
Plugin URI: http://maxime.sh/linked-articles
Description: Easily attach a link to a post. The post permalink is replaced with the shared link.
Author: Maxime Valette
Version: 1.0
Author URI: http://www.maximevalette.com/
*/
/*
            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                   Version 2, December 2004

Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

Everyone is permitted to copy and distribute verbatim or modified
copies of this license document, and changing it is allowed as long
as the name is changed.

           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

 0. You just DO WHAT THE FUCK YOU WANT TO.
*/

function linked_articles_default() {

    $o = array(
        'permalink_text' => '<p><a href="{{PERMALINK}}">Permalink</a></p>',
        'title_prefix' => '&#9733;',
        'custom_field' => 'title_url',
		'title_template' => '<h1><a href="{{LINK}}" rel="bookmark" title="{{TITLE}}">{{TITLE}}</a></h1>'
    );

    update_option('linked_articles', $o);

    return;

}

function linked_articles_title() {

    global $post;

    $thePostID = $post->ID;
    $post_id = get_post($thePostID);
    $title = get_the_title($post_id);
    $perm = get_permalink($post_id);
    $custom_field = linked_articles_option('custom_field');

    if ($url = get_post_meta($post->ID, $custom_field, true)) {
        $link = $url;
    } else {
        $link = $perm;
    }

    $prefix = linked_articles_option('title_prefix');
	$template = linked_articles_option('title_template');

	if ($link != $perm) {
		$title = $prefix.' '.$title;
	}

	$template = str_replace('{{LINK}}', $link, $template);
	$template = str_replace('{{TITLE}}', $title, $template);

	echo $template;

}

function linked_articles_link_rss($permalink) {

    global $wp_query;

    $custom_field = linked_articles_option('custom_field');

    if ($url = get_post_meta($wp_query->post->ID, $custom_field, true)) return $url;

    return $permalink;

}

function linked_articles_title_rss($title) {

    global $wp_query;

    $custom_field = linked_articles_option('custom_field');

    if ($url = get_post_meta($wp_query->post->ID, $custom_field, true)) {

        $prefix = linked_articles_option('title_prefix');
        $title = $prefix.' '.$title;

    }

    return $title;

}

function linked_articles_content_rss($content) {

    global $wp_query;

    $custom_field = linked_articles_option('custom_field');

    if (is_feed() && $url = get_post_meta($wp_query->post->ID, $custom_field, true)) {

        $permalink = esc_url( get_permalink() );
        $text = linked_articles_option('permalink_text');
        $text = str_replace('{{PERMALINK}}', $permalink, $text);

        $content .= "\n".$text;

    }

    return $content;

}

add_filter('the_title_rss', 'linked_articles_title_rss');
add_filter('the_content', 'linked_articles_content_rss');
add_filter('the_excerpt_rss', 'linked_articles_content_rss');
add_filter('the_permalink_rss', 'linked_articles_link_rss');

function linked_articles_option($option) {

    $o = get_option('linked_articles');

    return $o[$option];

}

function linked_articles_settings() {

	$message = null;

	if ($_POST) {

		$o = array();

		$o['title_prefix'] = stripslashes($_POST['title_prefix']);
		$o['permalink_text'] = stripslashes($_POST['permalink_text']);
		$o['custom_field'] = stripslashes($_POST['custom_field']);
		$o['title_template'] = stripslashes($_POST['title_template']);

		update_option('linked_articles', $o);

		$message = '<div class="updated"><p><strong>'._('Options saved.').'</strong></p></div>';

	}

    $o = get_option('linked_articles');

    $o['title_prefix'] = htmlspecialchars($o['title_prefix']);
    $o['permalink_text'] = htmlspecialchars($o['permalink_text']);
    $o['custom_field'] = htmlspecialchars($o['custom_field']);
	$o['title_template'] = htmlspecialchars($o['title_template']);

    echo <<<HTML

<div class="wrap">

{$message}

<div id="icon-link-manager" class="icon32"><br /></div>
<h2>Linked Articles Settings</h2>

<p>You can edit all the settings of the displayed text with Linked Articles.</p>

<form method="POST">

<table class="form-table">

<tr valign="top">
<th scope="row">
	<p>Title template:</p>
	<ul>
		<li>{{LINK}}: The URL (original permalink or shared link)</li>
		<li>{{TITLE}}: The original title of the post</li>
	</ul>
</th>
<td><textarea name="title_template" id="title_template" style='width:600px;height:100px'>{$o['title_template']}</textarea></td>
</tr>

<tr valign="top">
<th scope="row"><p>Title prefix:</p></th>
<td><input id="title_prefix" maxlength="255" size="30" name="title_prefix" value="{$o['title_prefix']}" /></td>
</tr>

<tr valign="top">
<th scope="row">
	<p>Permalink text:</p>
	<ul>
		<li>{{PERMALINK}}: The URL of the permalink</li>
	</ul>
</th>
<td><textarea name="permalink_text" id="permalink_text" style='width:600px;height:100px'>{$o['permalink_text']}</textarea></td>
</tr>

<tr valign="top">
<th scope="row"><p>Custom field name:</p></th>
<td><input id="custom_field" maxlength="45" size="30" name="custom_field" value="{$o['custom_field']}" /></td>
</tr>

</table>

<p class="submit">
<input class='button-primary' type='submit' name='Save' value="Save Options" id='submitbutton' />
</p>

</form>

<p>Plugin by <a href="http://maxime.sh">Maxime Valette</a>. Licensed under WTFPL.</p>

</div>

HTML;


}

function linked_articles_admin() {

    add_options_page('Linked Articles', 'Linked Articles', 1, 'linked_articles', 'linked_articles_settings');

}

register_activation_hook(__FILE__, 'linked_articles_default');
add_action('admin_menu','linked_articles_admin');