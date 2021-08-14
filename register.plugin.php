<?php
/**
 * Plugin Name: Telegram Bot
 * Plugin URI: https://github.com/heroe.soebekti/telegram
 * Description: Use for integrated with Telegram Bot 
 * Version: 1.0.0
 * Author: Heru Subekti
 * Author URI: https://github.com/heroe.soebekti
 */

// get plugin instance
require_once __DIR__ . '/lib/autoload.php';
//require_once __DIR__.'/lib/Locale.php';

$plugin = \SLiMS\Plugins::getInstance();

use SLiMS\DB;
use Lib\BotTelegram;

// registering menus
$plugin->registerMenu('opac', 'telegram', __DIR__ . '/webhook.php');

$plugin->registerMenu('membership', __('Telegram Account'), __DIR__ . '/account.php');

$plugin->registerMenu('system', __('Telegram Bot Settings'), __DIR__ . '/settings.php');

$plugin->registerMenu('system', __('Bot Auto Response'), __DIR__ . '/answer.php');

//register receipt transaction
$plugin->register('circulation_after_successful_transaction', 
    function ($data) {
     // receipt template
        $html_str  = '=================='.PHP_EOL;
        $html_str  = '<b>'.__('Print Circulation Receipt').'</b>'.PHP_EOL;
        $html_str .= '=================='.PHP_EOL;
        $html_str .= ' '.__('Member Name'). ' : <b>'.strtoupper($data['memberName']).'</b>'.PHP_EOL;
        $html_str .= ' '.__('Member ID').' : <i>'.$data['memberID'].'</i>'.PHP_EOL;
        $html_str .= ' '.__('Transaction finished').' : '.date("H:i:s d-m-Y").PHP_EOL;
        if(isset($data['loan'])){
            $html_str .= ' =================='.PHP_EOL;
            $html_str .= __('Loan').' :'.PHP_EOL;
            foreach ($data['loan'] as $key => $value) {
                //$html_str .= PHP_EOL;
                $html_str .= '┬─ <b>'.$value['title'].'</b>'.PHP_EOL;
                $html_str .= '├ '.__('Item Code').' : <i>'.$value['itemCode'].'</i> '.PHP_EOL;
                $html_str .= '├ '.__('Loan Date').'  : '.$value['loanDate'].PHP_EOL;
                $html_str .= '└ '.__('Return Date').'  : '.$value['dueDate'].PHP_EOL;
            }
            $html_str .= ' =================='.PHP_EOL;
        } 
        if(isset($data['return']) || isset($data['extend'])){
            if(isset($data['extend'])){
                $html_str .= ' =================='.PHP_EOL;
                $html_str .= __('Extend').' :'.PHP_EOL;
                foreach ($data['extend'] as $key => $value) {
                    //$html_str .= PHP_EOL;
                    $html_str .= '┬─ <b>'.$value['title'].'</b>'.PHP_EOL;
                    $html_str .= '├ '.__('Item Code').' : <i>'.$value['itemCode'].'</i> '.PHP_EOL;
                    $html_str .= '└ '.__('Return Date').' : '.$value['dueDate'].PHP_EOL;
                }
                $html_str .= ' =================='.PHP_EOL;
            }
            else{
                $html_str .= ' =================='.PHP_EOL;
                $html_str .= __('Return').' :'.PHP_EOL;
                foreach ($data['return'] as $key => $value) {
                    //$html_str .= PHP_EOL;
                    $html_str .= '┬─ <b>'.$value['title'].'</b>'.PHP_EOL;
                    $html_str .= '├ '.__('Item Code').' : <i>'.$value['itemCode'].'</i> '.PHP_EOL;
                    $html_str .= '├ '.__('Return Date').' : '.$value['returnDate'].PHP_EOL;
                    $overdues = '-';
                    if(is_array($value['overdues'])){
                        $overdues = sprintf(__('Overdue %d days with fines %s. %s'),$value['overdues']['days'], 'Rp', $value['overdues']['value']);

                    }
                    $html_str .= '└ '.__('Overdue').' : '.$overdues.PHP_EOL;
                }    
                $html_str .= ' =================='.PHP_EOL;            
            }
        } 
       
        $html_str .= 'pesan ini dari sistem dan merupakan bukti transaksi yang sah';

        //sending proccess
        $bot = new BotTelegram();
        if($bot->send_receipt() && $bot->haveAccount($data['memberID'])){
            if($bot->connected()){
                $status = $bot->direct_reply($data['memberID'],$html_str);
                if($status['ok']){
                    utility::jsToastr('Status Telegram', 'Bukti transaksi berhasil terkirim ke <b>'.$data['memberName'].'</b>', 'success');
                }else{
                    utility::jsToastr('Status Telegram', 'Bukti transaksi gagal terkirim ke <b>'.$data['memberName'].'</b> karena akun bot terhapus atau terblokir anggota', 'warning');                    
                }
            }
            else{
                utility::jsToastr('Status Telegram', 'Bukti transaksi gagal terkirim ke akun telegram <br/>Periksa koneksi internet !', 'error');
            }
        }
    }
);

