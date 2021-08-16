<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 08/02/2021 18:50
 * @File name           : BotTelegram.php
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

namespace Lib;

use SLiMS\DB;
use Lib\BotInterface;

//require __DIR__.DS.'locale.php';

class BotTelegram {

    protected $token;
    protected $parse_mode;
    protected $force_reply;
    public $send_receipt;

    /**
     * BotTelegram constructor.
     */
    
    function __construct(){
        $this->getSettings();
    }

    /**
     *
     */
    public function getSettings(){
        $query = DB::getInstance('mysqli')->query('SELECT * FROM telegram_settings');
        while ($config = $query->fetch_array())
        $this->token        = $config['token'];
        $this->parse_mode   = 'html'; // markdown or html
        $this->force_reply  = true;
        $this->send_receipt = $config['circ_receipt']??0;
        $this->header       = '';
        $this->footer       = '';
    }

    /**
     * @return bool
     */
    public function connected(){
       $connected = @fsockopen("api.telegram.org", 443); 
       if ($connected){       
          fclose($connected);
          return true;
       }
       return false;
    }

    /**
     * @param $memberID
     * @return bool
     */
    public function haveAccount($memberID){
        $query = DB::getInstance('mysqli')->query('SELECT * FROM member_custom WHERE member_id ="'.$memberID.'"');
        if($query->num_rows){
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function send_receipt(){
        $query = DB::getInstance('mysqli')->query('SELECT * FROM telegram_settings');
        while ($config = $query->fetch_array())
            if($config['circ_receipt']== '1'){
                return true;
            }
        return false;
    }

    /**
     * @param $memberID
     * @param $text
     */
    public function direct_reply($memberID, $text){
        $query = DB::getInstance('mysqli')->query('SELECT * FROM member_custom WHERE member_id ="'.$memberID.'"');
        while ($data = $query->fetch_assoc())
        return self::send_reply($data['telegram_id'], $text);
    }

    /**
     * @param string $method
     * @return string
     */
    private function request_url($method = 'SendMessage'){
        return "https://api.telegram.org/bot".$this->token."/". $method;
    }


    public function getMe(){
       $data = file_get_contents(self::request_url('getMe')); 
       $data = json_decode($data,false);
       if(isset($data->ok)){
            return $data->result;
       }
       return false;
    }

    public function getWebhookInfo(){
       $data = file_get_contents(self::request_url('getWebhookInfo')); 
       $data = json_decode($data,false);
        if(isset($data->ok)){
            return $data->result;
       }
       return false;
    }

    /**
     * @param $chat_id
     * @param $text
     * @param null $keyboard
     */
    public function send_reply($chat_id, $text, $keyboard = null){
        $data = [
            'chat_id'       => $chat_id,
            'text'          => $this->header.$text.$this->footer,
            'reply_markup'  => $keyboard,            
            'parse_mode'    => $this->parse_mode,
            'force_reply'   => $this->force_reply
        ];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        if($text != ''){
            return file_get_contents(self::request_url(), false, $context);
        }
    }

    /**
     * @return bool
     */
    public function run(){
        $entityBody  = file_get_contents('php://input');
        $message     = json_decode($entityBody, true);
        $_response   = new BotInterface($message);
        $_chat_id    = $_response->chat->id;
        $_text       = $_response->result();   
        $_keyboard   = $_response->keyboard;   
        if (isset($_response)) {
            self::send_reply($_chat_id, $_text, $_keyboard);
            return true;
        }
        return false;
    }

}