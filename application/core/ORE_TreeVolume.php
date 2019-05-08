<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_TreeVolume
 *
 * @package ore
 */
class ORE_TreeVolume extends ORE_Volume {
	public $tree_name_generate = 0;
	public $tree_key = 'id';
	public $tree_parent_id = 'parent_id';
	public $tree_label = 'label';
	public $tree_depth = 'depth';
}

