<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Custom CSS Backend
* @type: css
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-07-07 08:23:26
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: admin_head
* @load_as_file: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
body {
    background: #EAEAFFFF;
}
.striped>tbody>:nth-child(odd), ul.striped>:nth-child(odd) {
    background-color: #eff0ff;
}