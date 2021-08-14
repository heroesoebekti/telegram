<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// key to get full database access
define('DB_ACCESS', 'fa');

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
include __DIR__.DS.'lib/Locale.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

$php_self = $_SERVER['PHP_SELF'].'?'.http_build_query($_GET);


if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* telegram answer update process */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    $question = trim(strip_tags($_POST['question']));
    $response = trim(strip_tags($_POST['response']));
    // check form validity
    if (empty($question) OR empty($response)) {
        utility::jsAlert(__('Question or Response can\'t be empty'));
        exit();
    } else {
        $data['question'] = $dbs->escape_string($question);
        $data['response'] = $dbs->escape_string($response);
        $data['input_date'] = date('Y-m-d');

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = $dbs->escape_string(trim($_POST['updateRecordID']));
            // update the data
            $update = $sql_op->update('telegram_answer', $data, 'id=\''.$updateRecordID.'\'');
            if ($update) {
                utility::jsAlert(__('Data Successfully Updated'));
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { 
                utility::jsAlert(__('Language Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); 
            }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('telegram_answer', $data);
            if ($insert) {
                utility::jsAlert(__('New Data Successfully Saved'));
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$php_self.'\');</script>';
            } else { 
                utility::jsAlert(__('Language Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array($dbs->escape_string(trim($_POST['itemID'])));
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = $dbs->escape_string(trim($itemID));
        if (!$sql_op->delete('telegram_answer', "id='$itemID'")) {
            $error_num++;
        }
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsAlert(__('All Data Successfully Deleted'));
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$php_self.'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsAlert(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'));
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$php_self.'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* answer update process end */

/* search form */
?>

<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Telegram Auto Response'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo $php_self; ?>&action=none" class="btn btn-default"><?php echo __('Question List'); ?></a>
      <a href="<?php echo $php_self ?>&action=detail" class="btn btn-default"><?php echo __('Add New Question'); ?></a>
	  </div>
    <form name="search" action="<?php echo $php_self; ?>" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
    <input type="text" name="keywords" class="form-control col-md-3" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
    </form>
  </div>
</div>
</div>
<?php
/* search form end */

/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
    /* RECORD FORM */
    $itemID = $dbs->escape_string(trim(isset($_POST['itemID'])?$_POST['itemID']:0));
    $rec_q = $dbs->query("SELECT * FROM telegram_answer WHERE id='$itemID'");
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['question'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // language ID
    $form->addTextField('text', 'question', __('Question').'*', $rec_d['question']??'', 'class="form-control col-6"');
    // language_name
    $form->addTextField('text', 'response', __('Response').'*', $rec_d['response']??'', 'class="form-control col-12"');

     // edit mode messagge
    if ($form->edit_mode) {    
    echo '<div class="infoBox">'.__('You are going to edit data').' : <b>'.$rec_d['question'].'</b>  <br />'.__('Last Update').' '.$rec_d['input_date'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {

    // table spec
    $table_spec = 'telegram_answer AS t';

    // create datagrid
    $datagrid = new simbio_datagrid();

    $datagrid->setSQLColumn('t.id',
     't.question AS \''.__('Question').'\'', 
     't.response AS \''.__('Response').'\'');
    
    $datagrid->setSQLorder('t.question ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $datagrid->setSQLCriteria("t.question LIKE '%$keywords%' OR t.response LIKE '%$keywords%'");
    }

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $php_self;

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
