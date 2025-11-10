<?php
// mu-plugins/classic-on-posts-only.php
add_filter('use_block_editor_for_post_type', fn($use, $pt) => $pt === 'page' ? true : false, 999, 2);