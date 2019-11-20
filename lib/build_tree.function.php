<?php
function build_tree($elements, $parent_id = 0) {
  $branch = array();
  foreach ($elements as $element) {
    if ($element['parent_id'] == $parent_id) {
      $children = build_tree($elements, $element['id']);
      if ($children) {
        $element['children'] = $children;
      }
      $branch[] = $element;
    }
  }
  return $branch;
}
 ?>
