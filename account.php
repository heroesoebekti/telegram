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

require_once __DIR__ . '/lib/autoload.php';

use Lib\BotTelegram;

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
$can_read = utility::havePrivilege('membership', 'r');
$can_write = utility::havePrivilege('membership', 'w');

$php_self = $_SERVER['PHP_SELF'].'?'.http_build_query($_GET);


if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

if(isset($_POST['sendData'])){
    $msg = new BotTelegram();
    $status = $msg->direct_reply($_POST['memberID'], $_POST['msg']);
    if($status['ok']){
        utility::jsalert(__('Message Sent Successfully!'));
    }
}

if(isset($_GET['options']) && $_GET['options'] == 'dm'){
    ob_start();
    // create new instance
    $form = new simbio_form_table_AJAX('sendingForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="sendData" value="' . __('Send') . '" class="s-btn btn btn-default btn-sm"';
    // form table attributes
    $form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell"';
    $form->table_content_attr = 'class="alterCell2"';
    $form->addHidden('memberID', $_GET['memberID']);
    $form->addTextField('textarea', 'msg', __('Message') . '*','', 'rows="3" class="form-control"');
    // print out the form object
    echo $form->printOut();            
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/pop_iframe_tpl.php';
    exit();
}

 if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
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
        $data['telegram_id'] = "NULL";
        $update = $sql_op->update('member_custom', $data, 'member_id=\''.$itemID.'\'');
        if (!$update) {
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

/* search form */
?>

<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
    <div class="per_title">
        <h2><?php echo __('Telegram Account'); ?></h2>
  </div>
    <div class="sub_section">
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
    // table spec
    $table_spec = 'member_custom mc LEFT JOIN member m ON m.member_id=mc.member_id';
    $criteria   = 'mc.telegram_id IS NOT NULL';

    // create datagrid
    $datagrid = new simbio_datagrid();

    $datagrid->setSQLColumn('mc.member_id',
     'm.member_id AS \''.__('Member ID').'\'', 
     'm.member_name AS \''.__('Member Name').'\'',
     'mc.telegram_username AS \''.__('Telegram Username').'\'',
     'mc.telegram_id AS \''.__('Direct Message').'\'');
    
    $datagrid->setSQLorder('m.member_id ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $criteria .= " AND m.member_name LIKE '%$keywords%' OR m.member_id LIKE '%$keywords%'";
    }

    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $php_self;
    $datagrid->edit_property = false;
    $datagrid->modifyColumnContent(4, 'callback{sendMessage}');

    function sendMessage($obj_db, $array_data){
        $memberID = $array_data[0];
        return '<a class="btn btn-sm btn-primary notAJAX openPopUp" href="'.$_SERVER['PHP_SELF'].'?'.http_build_query($_GET).'&options=dm&memberID='.$memberID.'" title="'.__('Direct Message').'">'.__('Write Message').'</a>';
    }
    
    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;

/* main content end */
