<?php
/*
Plugin Name: Moo Collapsing Categories
Plugin URI: http://www.3dolab.net/en/mootools-collapsing-categories-and-archives
Description: Allows users to expand and collapse categories with MooTools. <a href='options-general.php?page=collapsArch.php'>Options and Settings</a> 
Author: 3dolab
Version: 0.5.8
Author URI: http://www.3dolab.net

This work is largely based on the Collapsing Categories plugin by Robert Felty
(http://robfelty.com), which was also distributed under the GPLv2.
His website has lots of informations.

This file is part of Moo Collapsing Categories

    Moo Collapsing Categories is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Moo Collapsing Archives is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Collapsing Categories; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$url = get_settings('siteurl');
global $collapsCatVersion;
$collapsCatVersion = '0.5.8';

if (!is_admin()) {
  $inFooter = get_option('collapsCatInFooter');
  $MTversion = get_option('MTversion');
  if ($MTversion == '12'){
  wp_register_script('moocore', WP_PLUGIN_URL . '/'.dirname( plugin_basename(__FILE__)).'/js/mootools-1.2.5-core-yc.js', false, '1.2.5');
  wp_register_script('moomore', WP_PLUGIN_URL . '/'.dirname( plugin_basename(__FILE__)).'/js/mootools-1.2.5.1-more-yc.js', false, '1.2.5');
  wp_enqueue_script('moocore');
  wp_enqueue_script('moomore');
  wp_enqueue_script('collapsFunctions', WP_PLUGIN_URL . '/'.dirname( plugin_basename(__FILE__)).'/js/collapsFunctions.js', array('moocore','moomore'), '1.2.5');
  } elseif ($MTversion == '13'){
  wp_register_script('moocore', WP_PLUGIN_URL . '/'.dirname( plugin_basename(__FILE__)).'/js/mootools-core-1.3.2-full-nocompat-yc.js', false, '1.3.2');
  wp_register_script('moomore', WP_PLUGIN_URL . '/'.dirname( plugin_basename(__FILE__)).'/js/mootools-more-1.3.2.1-yc.js', false, '1.3.2');
  wp_enqueue_script('moocore');
  wp_enqueue_script('moomore');
  wp_enqueue_script('collapsFunctions', WP_PLUGIN_URL . '/'.dirname( plugin_basename(__FILE__)).'/js/collapsFunctions-1.3.js', array('moocore','moomore'), '1.3.2');
  }
  add_action( 'wp_head', array('collapsCat','get_head'));
//  add_action( 'wp_footer', array('collapsCat','get_foot'));
} else {
  // call upgrade function if current version is lower than actual version
  $dbversion = get_option('collapsCatVersion');
  if (!$dbversion || $collapsCatVersion != $dbversion)
    collapscat::init();
}
add_action('admin_menu', array('collapsCat','setup'));
add_action('init', array('collapsCat','init_textdomain'));
register_activation_hook(__FILE__, array('collapsCat','init'));

class collapsCat {
	function init_textdomain() {
	  $plugin_dir = basename(dirname(__FILE__)) . '/languages/';
	  //load_plugin_textdomain( 'mootools-collapsing-categories', WP_PLUGIN_DIR . $plugin_dir, $plugin_dir );
	  load_plugin_textdomain( 'moo-collapsing-cat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function init() {
    global $collapsCatVersion;
	  include('collapsCatStyles.php');
		$defaultStyles=compact('selected','default','block','noArrows','custom');
    $dbversion = get_option('collapsCatVersion');
    if ($collapsCatVersion != $dbversion && $selected!='custom') {
      $style = $defaultStyles[$selected];
      update_option( 'collapsCatStyle', $style);
      update_option( 'collapsCatVersion', $collapsCatVersion);
    }
    if( function_exists('add_option') ) {
      update_option( 'collapsCatOrigStyle', $style);
      update_option( 'collapsCatDefaultStyles', $defaultStyles);
    }
    if (!get_option('collapsCatOptions')) {
      include('defaults.php');
      update_option('collapsCatOptions', $defaults);
    }
    if (!get_option('collapsCatStyle')) {
      add_option( 'collapsCatStyle', $style);
		}
    if (!get_option('collapsCatSidebarId')) {
      add_option( 'collapsCatSidebarId', 'sidebar');
		}
    if (!get_option('collapsCatVersion')) {
      add_option( 'collapsCatVersion', $collapsCatVersion);
		}
    if (!get_option('MTversion')) {
      add_option( 'MTversion', $MTversion);
		}

	}

	function setup() {
		if( function_exists('add_options_page') ) {
      if (current_user_can('manage_options')) {
				add_options_page(__('Moo Collapsing Categories', 'moo-collapsing-cat'),
            __('Moo Collapsing Categories', 'moo-collapsing-cat'),1,
            basename(__FILE__),array('collapscat','ui'));
			}
		}
	}
	function ui() {
		include_once( 'collapscatUI.php' );
	}

	function get_head() {
    $style=stripslashes(get_option('collapsCatStyle'));
    echo "<style type='text/css'>
    ".$style."
    </style>\n
    <!--[if lte IE 8]>
      <style type='text/css'>
	  #".get_option('collapsCatSidebarId')." ul li, #".get_option('collapsCatSidebarId')." ul.collapsing.categories.list li.collapsing.categories.item {
		text-indent:0;
		padding:0;
		margin:0;
	  } 
      </style>
    <![endif]-->";
	}
  function phpArrayToJS($array,$name) {
    /* generates javscript code to create an array from a php array */
    $script = "try { $name" . 
        "['catTest'] = 'test'; } catch (err) { $name = new Object(); }\n";
    foreach ($array as $key => $value){
      $script .= $name . "['$key'] = '" . 
          addslashes(str_replace("\n", '', $value)) . "';\n";
    }
    return($script);
  }
}


include_once( 'collapscatlist.php' );
function collapsCat($args='', $print=true) {
  global $collapsCatItems;
  if (!is_admin()) {
    list($posts, $categories, $parents, $options) = 
        get_collapscat_fromdb($args);
    list($collapsCatText, $postsInCat) = list_categories($posts, $categories,
        $parents, $options);
    $url = get_settings('siteurl');
    if ($print) {
      print($collapsCatText);
      echo "<li style='display:none'><script type=\"text/javascript\">\n";
      echo "// <![CDATA[\n";
      echo '/* These variables are part of the Moo Collapsing Categories Plugin 
      *  Version: 0.5.5
      * Copyright 2011 3DO lab (3dolab.net)
      */' . "\n";
      global $expandSym,$collapseSym,$expandSymJS, $collapseSymJS, 
      $wpdb,$options,$wp_query, $autoExpand, $postsToExclude, 
      $postsInCat;
  include('defaults.php');
  $options=wp_parse_args($args, $defaults);
  extract($options);
  if ($expand==1) {
    $expandSym='+';
    $collapseSym='—';
  } elseif ($expand==2) {
    $expandSym='[+]';
    $collapseSym='[—]';
  } elseif ($expand==3) {
    $expandSym="<img src='". get_settings('siteurl') .
         "/wp-content/plugins/mootools-collapsing-categories/" . 
         "img/expand.gif' alt='expand' />";
    $collapseSym="<img src='". get_settings('siteurl') .
         "/wp-content/plugins/mootools-collapsing-categories/" . 
         "img/collapse.gif' alt='collapse' />";
  } elseif ($expand==4) {
    $expandSym=$customExpand;
    $collapseSym=$customCollapse;
  } else {
    $expandSym='&#9658;';
    $collapseSym='&#9660;';
  }
  if ($expand==3) {
    $expandSymJS='expandImg';
    $collapseSymJS='collapseImg';
  } else {
    $expandSymJS=$expandSym;
    $collapseSymJS=$collapseSym;
  }
   $expandText= __('click to expand', 'moo-collapsing-cat');
   $collapseText= __('click to collapse', 'moo-collapsing-cat');
    echo "var expandSym=\"$expandSym\";";
    echo "var collapseSym=\"$collapseSym\";";
    echo "var expand=\"$expandSymJS\";";
    echo "var collapse=\"$collapseSymJS\";";
    echo "var animate=\"$animate\";";
    echo "var expandText=\"$expandText\";";
    echo "var collapseText=\"$collapseText\";";
    echo "var useCookies=\"$useCookies\";";
    foreach ($autoExpand as $expandedCat){
	    $expandCookieCat = "Cookie.write('collapsCat-".$expandedCat.":".$number."', 'inline');";
	    echo $expandCookieCat;
    }
      // now we create an array indexed by the id of the ul for posts
      echo collapsCat::phpArrayToJS($collapsCatItems, 'collapsItems');
      echo "// ]]>\n</script></li>\n";
    } else {
      return(array($collapsCatText, $postsInCat));
    }
  }
}
$version = get_bloginfo('version');
if (preg_match('/^(2\.[8-9]|[3-9]\..*)/', $version))
  include('collapscatwidget.php');
?>