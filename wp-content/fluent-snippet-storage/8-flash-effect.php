<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Flash Effect
* @type: css
* @status: draft
* @created_by: 
* @created_at: 
* @updated_at: 
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: wp_head
* @load_as_file: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
.flash-image::before {
	position: absolute;
    top: 0;
    left: 0;
    width: 130%;
    height: 120%;
    background: rgba(255, 255, 255, 0.5);
    content: '';
    transition: transform 0.7s;
    transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, -110%, 0);
    z-index: 5;
}

.flash:hover .flash-image::before{
      transform: scale3d(1.9, 1.4, 1) rotate3d(0, 0, 1, 45deg) translate3d(0, 110%, 0);
}