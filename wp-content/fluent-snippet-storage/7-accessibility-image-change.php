<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Accessibility Image Change
* @type: js
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
<style>
    /*Accessibility Image CSS*/
    .pojo-a11y-toolbar-toggle img {
    width: 60px;
    height: auto; 
}
</style>
document.addEventListener('DOMContentLoaded', function() {
    const svgElement = document.querySelector('.pojo-a11y-toolbar-toggle svg');
    if (svgElement) {
        const imgElement = document.createElement('img');
        imgElement.src = '...'; // Put Your Image Link here! Inside the quotation mark, remove 3 dots.
        imgElement.alt = 'כלי נגישות';
        imgElement.width = svgElement.getAttribute('width'); 

        const parent = svgElement.parentElement;
        parent.replaceChild(imgElement, svgElement);
    }
});