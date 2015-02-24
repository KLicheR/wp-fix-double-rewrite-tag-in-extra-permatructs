<?php
/*
Plugin Name: Fix Double Rewrite Tag in Extra Permatructs
Plugin URI: https://github.com/KLicheR/wp-fix-double-rewrite-tag-in-extra-permatructs
Description: Fix permastructure when you already use rewrite tag in rewrite slug.
Version: 0.0.1
Author: KLicheR
Author URI: https://github.com/KLicheR
License: GPLv2 or later
*/

/*  Copyright 2015  Kristoffer Laurin-Racicot  (email : kristoffer.lr@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Fix_Double_Rewrite_Tag_In_Extra_Permatructs {
	private $taxonomies_to_fix;

	public function __construct() {
		// Get the list of taxonomies to fix.
		$this->taxonomies_to_fix = apply_filters('taxonomies_extra_structure_to_fix', array());

		// Fix the permastructs for link generation (ex.: "get_term_link").
		add_action('wp', array($this, 'fix_taxonomies_extra_permastruct'), 1);

		// Fix the permastructs to regenerate the rewrite rules.
		foreach ($this->taxonomies_to_fix as $taxonomy_to_fix) {
			add_filter("{$taxonomy_to_fix}_rewrite_rules", array($this, 'fix_taxonomy_extra_permastruct'), 10);
		}
	}

	public function fix_taxonomies_extra_permastruct() {
		foreach ($this->taxonomies_to_fix as $taxonomy_to_fix) {
			$this->fix_taxonomy_extra_permastruct(null, $taxonomy_to_fix);
		}
	}

	public function fix_taxonomy_extra_permastruct($rules = null, $taxonomy_to_fix = null) {
		// If it's called by a "{taxo}_rewrite_rules" filter, we need to identified
		// the targeted taxo by checking the filter name.
		if (
			is_null($taxonomy_to_fix)
			&& isset($GLOBALS['wp_current_filter'])
			&& isset($GLOBALS['wp_current_filter'][0])
			&& preg_match('/(.*)_rewrite_rules$/', $GLOBALS['wp_current_filter'][0], $matches)
		) {
			$taxonomy_to_fix = $matches[1];
		}

		// Load the taxonomy object...
		$taxonomy = get_taxonomy($taxonomy_to_fix);
		// ...and check if there isn't another place where the taxonomy is set...
		// Ex.: "/%a_taxo_name%/something" that would normally result in "/%a_taxo_name%/something/%a_taxo_name%".
		if (strpos($taxonomy->rewrite['slug'], "%{$taxonomy->name}%") === false) {
			return $rules;
		}
		// echo('<pre>');var_dump('fix_taxonomy_extra_permastruct', 'avant', $rules);//exit;

		global $wp_rewrite;

		// Remove the old permastructs.
		unset($wp_rewrite->extra_permastructs[$taxonomy->name]);
		// Create a permastructure without appending the taxonomy at the end like WordPress would do.
		add_permastruct( $taxonomy->name, "{$taxonomy->rewrite['slug']}", $taxonomy->rewrite );
		// Get the new structure.
		$struct = $wp_rewrite->extra_permastructs[$taxonomy->name];
		// Generate the new rules with it.
		$new_rules = $wp_rewrite->generate_rewrite_rules( $struct['struct'], $struct['ep_mask'], $struct['paged'], $struct['feed'], $struct['forcomments'], $struct['walk_dirs'], $struct['endpoints'] );

		// echo('<pre>');var_dump('fix_taxonomy_extra_permastruct', 'après', $new_rules);//exit;
		return $new_rules;
	}
}

new Fix_Double_Rewrite_Tag_In_Extra_Permatructs();