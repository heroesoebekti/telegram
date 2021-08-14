<?php 

use SLiMS\DB;

require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
include __DIR__.DS.'lib/Locale.php';


$php_self = $_SERVER['PHP_SELF'].'?'.http_build_query($_GET);

if(isset($_POST['updateData'])){

    $sql_op = new simbio_dbop($dbs);
    $token = trim($dbs->escape_string(strip_tags($_POST['bot_token'])));
    $old_settings = trim($dbs->escape_string(strip_tags($_POST['old_settings']))); 
      if (empty($token)) {
        utility::jsToastr('Telegram Bot', __('TOKEN and Bot Name can not be empty'), 'error');
        exit();
    }
    else{
      $data['token'] = $token;
      $data['self_regis'] = $_POST['registration'];
      $data['self_extend'] = $_POST['extended'];
      $data['circ_receipt'] = $_POST['receipt'];
      // update data
      $update = $sql_op->update('telegram_settings', $data, 'token=\'' . $old_settings.'\'');
      // send an alert
      if ($update) {
        utility::jsToastr('Telegram Bot', __('Data Successfully Updated'), 'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';

        //echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$php_self.'\');</script>';
      }else{
        utility::jsToastr('Telegram Bot', __('Data FAILED to Updated. Please Contact System Administrator') . "\n" . $sql_op->error, 'error');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
        //echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$php_self.'\');</script>';
      }
    }
}

?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('Bot Settings'); ?></h2>
    </div>
    <div class="infoBox">
      <?= __('Modify telegram bot preferences') ?>
    </div>

    <div class="card text-white bg-success mb-3 p-2 m-2 telegram-info">
      <div class="card-body">
        <h6 class="card-title"><?= __('Bot Telegram Status')?></h6>
          <div class="row status">
          </div>
          <button class="btn btn-outline-light mt-4 telegram-connect" style="border-radius: 20px;"><i class="fa fa-plug" aria-hidden="true"></i>&nbsp;<?= __('CONNECT')?></button> 
          
          <!-- Button trigger modal -->
<button type="button" class="btn btn-outline-light float-right" data-toggle="modal" data-target="#help" style="border-radius: 20px;"><?= __('Help')?></button>

      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="help" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<?php

  $query = DB::getInstance('mysqli')->query('SELECT * FROM telegram_settings');
  while ($config = $query->fetch_assoc()) $telegram = $config;

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $php_self, 'post');
$form->submit_button_attr = 'name="updateData" value="'.__('Save').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

$form->addTextField('text', 'bot_token', __('Token'), $telegram['token'], 'class="form-control col-6"');

$form->addHidden('old_settings',  $telegram['token']);

$options_registration = null;
$options_registration[] = array('0', __('Disable'));
$options_registration[] = array('1', __('Enable'));
$form->addSelectList('registration', __('Self Activation'), $options_registration, $telegram['self_regis']??'0','class="form-control col-3"');

$options_extended = null;
$options_extended[] = array('0', __('Disable'));
$options_extended[] = array('1', __('Enable'));
$form->addSelectList('extended', __('Self Loan Extension'), $options_extended, $telegram['self_extend']??'0',' class="form-control col-3"');

$options_reservation = null;
$options_reservation[] = array('0', __('Disable'));
$options_reservation[] = array('1', __('Enable'));
$form->addSelectList('reservation', __('Self Booking'), $options_reservation, $sysconf['reserve_on_loan_only']??'0',' disabled class="form-control col-3"');

$options_receipt = null;
$options_receipt[] = array('0', __('Disable'));
$options_receipt[] = array('1', __('Enable'));
$form->addSelectList('receipt', __('Circulation Receipt'), $options_receipt, $telegram['circ_receipt']??'0','class="form-control col-3"');

$options_visitor = null;
$options_visitor[] = array('0', __('Disable'));
$options_visitor[] = array('1', __('Enable'));
$form->addSelectList('visit', __('Visiting Notification'), $options_visitor, $sysconf['reserve_on_loan_only']??'0',' disabled class="form-control col-3"');

$options_overdue_warning = null;
$options_overdue_warning[] = array('0', __('Disable'));
$options_overdue_warning[] = array('1', __('Enable'));
$form->addSelectList('visit1', __('Overdue Warning'), $options_overdue_warning, $sysconf['overdue_warning']??'0',' disabled class="form-control col-3"');

$options_overdue_notice = null;
$options_overdue_notice[] = array('0', __('Disable'));
$options_overdue_notice[] = array('1', __('Enable'));
$form->addSelectList('visit2', __('Overdue Notice'), $options_overdue_notice, $sysconf['overdue_notice']??'0',' disabled class="form-control col-3"');

// print out the object
echo $form->printOut();

$url = ($_SERVER['HTTP_X_FORWARDED_PROTO']??$_SERVER['REQUEST_SCHEME']).'://'.$_SERVER['SERVER_NAME'].SWB.'index.php?p=telegram';

?>
<script>
$(function() {
    $.get("https://api.telegram.org/bot<?=$telegram['token']?>/getWebhookInfo", function(data){
    var datas =  JSON.parse(JSON.stringify(data));
       if(datas.ok){
           
          $.get("https://api.telegram.org/bot<?=$telegram['token']?>/getMe", function(dataMe){
            var me =  JSON.parse(JSON.stringify(dataMe));
            //console.log(dataMe);
            $('.status').append('<div class="col-4"><?= __('Bot Name')?></div><div class="col-8"><a class="btn btn-sm btn-warning" href="https://t.me/'+me.result.username+'" target="_BLANK">'+me.result.first_name+'</a></div>');
          });
          
            $('.telegram-info').removeClass('bg-success').addClass('bg-dark');
            if(datas.result.url != '<?=$url?>'){
              $('.status').append('<div class="col-4">Status</div><div class="col-8"><?= __('webhook not provided for this server')?></div>');
            }else{
              $('.status').append('<div class="col-4">Status</div><div class="col-8">OK</div>');    
              $('.telegram-connect').hide();         
            }  
            $('.status').append('<div class="col-4"><?= __('Pending Update Count')?></div><div class="col-8">'+datas.result.pending_update_count+'</div>');
            $('.status').append('<div class="col-4"><?= __('Has Custom Certificate')?></div><div class="col-8">'+datas.result.has_custom_certificate+'</div>');
            $('.status').append('<div class="col-4"><?= __('Max Connections')?></div><div class="col-8">'+datas.result.max_connections+'</div>');         
        }
        else{
            $('.status').append('<div class="col-4"><?= __('Max Connections')?></div><div class="col-8">dibutuhkan sambungan internet</div>');         
        }

    });

    $('.telegram-connect').click(function() {
        $.ajax({
        type: "GET",
        url: 'https://api.telegram.org/bot<?=$telegram['token']?>/setWebhook?url=<?=$url?>',
          success: function(data){
              toastr.success('<?= __('Bot Authorized!')?>','Bot Telegram');
              location.reload();
            },
            statusCode: {
            404: function() {
              toastr.warning('<?= __('Bad Request: bad webhook: HTTPS url must be provided for webhook!')?>','Bot Telegram');
            },
            400: function() {
              toastr.warning('<?= __('Bad Request: bad webhook: HTTPS url must be provided for webhook!')?>', 'Bot Telegram');
           }
          }
      });
    });
});
</script>