<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Sitewide Details CSS
* @type: css
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-07-01 08:55:53
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: admin_head
* @load_as_file: yes
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
/* General Styling for Sitewide Details */
#sitewide-details.sitewide-details-wrap {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

#sitewide-details .sitewide-details-title {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

#sitewide-details .sitewide-details-intro {
    text-align: center;
    color: #666;
}

#sitewide-details .sitewide-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

#sitewide-details .sitewide-details-field {
    display: flex;
    flex-direction: column;
}

#sitewide-details .sitewide-details-label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #555;
}

#sitewide-details .sitewide-details-input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    color: #333;
}

#sitewide-details .sitewide-details-input:focus {
    border-color: #0073aa;
    box-shadow: 0 0 5px rgba(0,115,170,0.5);
    outline: none;
}

#sitewide-details .sitewide-details-button {
    background: #0073aa;
    border: none;
    color: #fff;
    cursor: pointer;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    margin-top: 20px;
}

#sitewide-details .sitewide-details-button:hover {
    background: #005b8c;
}

/* Additional styling for the add new field section */
#sitewide-details .add-new-field {
    margin-top: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

#sitewide-details .add-new-field .sitewide-details-input {
    margin-bottom: 10px;
}

#sitewide-details .add-new-field .sitewide-details-button {
    background: #28a745;
    grid-column: 1 / -1;
}

#sitewide-details .add-new-field .sitewide-details-button:hover {
    background: #218838;
}
