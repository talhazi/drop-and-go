<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Hide Accessibility on Bricks Builder
* @type: PHP
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-06-09 11:19:19
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: all
* @load_as_file: 
* @condition: {"status":"yes","run_if":"assertive","items":[[{"source":["user","authenticated"],"operator":"=","value":"yes"}]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
if (isset($_GET['bricks']) && $_GET['bricks'] === 'run') {
    echo '<style>.pojo-a11y-toolbar-left { display: none !important; }</style>';
}