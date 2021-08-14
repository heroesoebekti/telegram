<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 08/02/2021 18:50
 * @File name           : BotInterface.php
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

use api;
use SLiMS\DB;
use Lib\Keyboard;
use Lib\CirculationLib;


require __DIR__.DS.'Locale.php';

class BotInterface{

    public $response;
    public $member;

    /**
     * GetResponse constructor.
     * @param $arr
     */
    public function __construct($arr){
        $this->keyboard = '';
        $this->is_member = false;
        $this->callbackQuery($arr);
        $this->memberStatus();
    }

    /**
     *
     */
    protected function memberStatus(){
        $query = sprintf('SELECT m.*, mc.telegram_id as telegram FROM member_custom mc 
        	LEFT JOIN member m ON m.member_id=mc.member_id WHERE mc.telegram_id = \'%s\'',$this->chat->id);
        $_q = DB::getInstance('mysqli')->query($query);
        if($_q->num_rows){
            $this->is_member = true;
            while ($_d = $_q->fetch_assoc()) 
        	$this->member = $_d;          
        }   	
    }

    /**
     * @param $arr
     */
    protected function callbackQuery($arr){
        $this->response = isset($arr['callback_query'])?$arr['callback_query']['message']:$arr['message'];
        $this->callback = isset($arr['callback_query'])?$arr['callback_query']['data']:false;
    	$this->chat	    = (object)$this->response['chat'];
    }

    /**
     * @return false|string
     */
    public function result(){
    	switch ($this->callback) {
    			case '#reg':    				
    				$result = self::regMember();
    				break;

    			case '#extend':    				
    				$result = self::extend();
    				break;

    			case '#account':
    				$result = self::detailMember();
    				break;

    			case '#help':
    				$result = self::help();
    				break; 

    			case '#loan':
    				$result = self::loan();
    				break;

    			case '#overdue':
    				$result = self::overdue();
    				break;

    			case '#fines':
    				$result = self::fines();
    				break;

    			default:
    				$result = self::text_response();
    				break;
    		}
    	if($this->chat->type == 'private'){
    		return $result;
    	}
    	return false;
    }

    /**
     * @return false|string
     */
    protected function text_response(){
        global $sysconf;
		// check command previous
    	$query = 'SELECT * FROM telegram_chat_log WHERE chat_id = \''.$this->chat->id.'\' LIMIT 1';
    	$_q =  DB::getInstance('mysqli')->query($query);	
    	if($_q->num_rows){
    		while($_d = $_q->fetch_assoc()){
		    	switch ($_d['callback']) {
		    			case '#reg':    				
		    				$result = self::regMember();
		    				break;

		    			case '#extend':    				
		    				$result = self::extend();
		    				break;

		    			default:
							return false;
		    				break;
		    		}    			
    		}
    	}		
    	else{ 
    	// get response from database if exist
            $this->keyboard = Keyboard::keyboardLayout($this->is_member);
            if($this->response['text'] == '/start'){
                $string = $sysconf['library_name'];
            	$result = sprintf(__('Welcome to %s library'),$string);
            }else{
	    	$_q = DB::getInstance('mysqli')->query('SELECT * FROM telegram_answer WHERE question LIKE \''.$this->response['text'].'\' LIMIT 1');	
	    	if(!$_q->num_rows){
	    		return __('I don\'t understand');
	    	}
	    	$telegram = $_q->fetch_array()[2];
	        $response = explode(',', $telegram);
	        $result =  $response[mt_rand(0, count($response) - 1)]; // set random response
	    	}
    	}
		return $result;
	}


    /**
     * @return false|string
     */
    public function detailMember()
    {
        self::clearState();  
        $this->keyboard = Keyboard::keyboardLayout($this->is_member);
    	$member = api::member_load(DB::getInstance('mysqli'), $this->member['member_id']??null);
    	$str = __('UNREGISTRED member');
    	if($member){
    		$_data_member = $member[0];
    	    $str  = __('Your Account :').PHP_EOL;
            $str .= '======================='.PHP_EOL;
    	    $str .= __('Member ID').' : '.$_data_member['member_id'].PHP_EOL;
    	    $str .= __('Member Name').' : <b>'.strtoupper($_data_member['member_name']).'</b>'.PHP_EOL;
            $str .= __('Gender').' : '.($_data_member['member_name']==0?__('Male'):__('Female')).PHP_EOL;
            $str .= __('Birth Date').' : '.$_data_member['birth_date'].PHP_EOL;
            $str .= __('Member Type Name').' : '.$_data_member['member_type_name'].PHP_EOL;
            $str .= __('Member Address').' : '.$_data_member['member_address'].PHP_EOL;
            $str .= __('Member Mail Address').' : '.$_data_member['member_mail_address'].PHP_EOL;
            $str .= __('Member Email').' : '.$_data_member['member_email'].PHP_EOL;
            $str .= __('Postal Code').' : '.$_data_member['postal_code'].PHP_EOL;
            $str .= __('Member Phone').' : '.$_data_member['member_phone'].PHP_EOL;
            $str .= __('Member Since Date').' : '.$_data_member['member_since_date'].PHP_EOL;
            $str .= __('Register Date').' : '.$_data_member['register_date'].PHP_EOL;
            $str .= __('Expire Date').' : '.$_data_member['expire_date'].PHP_EOL;
            $str .= __('Is Pending').' : '.($_data_member['is_pending']==0?__('No'):__('Yes')).PHP_EOL;
            $str .= __('Member Notes').' : '.$_data_member['member_notes'].PHP_EOL;
        }
    	return $str;
    }


    protected function extend()
    {
        $this->keyboard = Keyboard::unsetKeyboard();
        if(!$this->is_member){
            return __('Only for registered account');           
        }
        $str = '';
        $_query = "SELECT * FROM telegram_chat_log WHERE callback = '#extend' AND chat_id=".$this->chat->id;
        $_q = DB::GetInstance('mysqli')->query($_query);
        if($_q->num_rows < 1){
            @DB::getInstance('mysqli')->query("INSERT IGNORE INTO `telegram_chat_log` (`chat_id`,`callback`, `data`) 
            VALUES ('".$this->chat->id."','#extend','');");        
            return __('Insert Item Code');
        }else{
            $_d = $_q->fetch_assoc();
            $_data = unserialize($_d['data']);   
            $_temp_data['item_code'] = $_data['item_code']??$this->response['text'];  
            $item_status = CirculationLib::checkLoan($this->member['member_id'],$_temp_data['item_code']);
            if(!$item_status){
                self::clearState(); 
                return __('Item not registered in database');
            }else{
                switch ($item_status) {

                    case 'expire':    
                        self::clearState();                
                    return __('Membership status is suspended or expired, Transaction cannot be continued');

                    case 'reserve':     
                        self::clearState();            
                    return __('Collection is currently in reserve, Transaction cannot be continued');

                    case 'overdue':     
                        self::clearState();            
                    return __('Collection status is overdue, Transaction cannot be continued');

                    default:
                        if(is_array($_data) && array_key_exists('item_code',$_data)){
                            if(strtoupper($this->response['text']) == 'Y'){
                                $extend = CirculationLib::extend($this->member['member_id'],$_temp_data['item_code']);
                                switch ($extend) {
                                    case 'max_reborrow':    
                                        self::clearState();                
                                        return __('Maximum Reborrow Limit, Transaction cannot be continued');

                                    case 'success':     
                                        self::clearState();            
                                        return __('Transaction Success, Check on Loans Menu');
                                }
                            }else{
                                self::clearState();     
                                return  __('Transaction Aborted');
                            }           
                        }
                        $data = serialize($_temp_data);
                        @DB::getInstance('mysqli')->query("UPDATE telegram_chat_log SET data = '".$data."' WHERE chat_id='".$this->chat->id."'");
                    return sprintf(__("Are you sure to extend <b> %s </b>? \nPress <code>Y</code> if agree or any key to abort!"),$item_status['title']);                                                
                }                 
            }
        }
        return $str;
    }

    /**
     * @return false|string
     */
    protected function regMember()
    {
    	$this->keyboard = Keyboard::unsetKeyboard();
    	if( !empty($this->member['telegram'])){
    		return __('Account already registered');
    	}

    	$_query = "SELECT * FROM telegram_chat_log WHERE callback = '#reg' AND chat_id=".$this->chat->id;
    	$_q = DB::GetInstance('mysqli')->query($_query);
    	if($_q->num_rows < 1){
           	@DB::getInstance('mysqli')->query("INSERT IGNORE INTO `telegram_chat_log` (`chat_id`,`callback`, `data`) 
            VALUES ('".$this->chat->id."','#reg','');");    	
        	return __('Insert your username');
    	}else{
    		$_d = $_q->fetch_assoc();
    		$_data = unserialize($_d['data']);
    		$_temp_data['username'] = $_data['username']??$this->response['text'];
    		$str = '';
    		if(array_key_exists('username', $_data)){
    			if(array_key_exists('password',$_data)){
    				$this->keyboard = Keyboard::keyboardLayout($this->is_member);
    				//get member data
    				$_member_q = DB::getInstance('mysqli')->query("SELECT m.member_id, m.mpasswd FROM member m WHERE m.member_id='".$_data['username']."'");
    				if(!$_member_q->num_rows){
                        self::clearState();
    					$str = __('FAILED, Check your username and password or maybe already registered with another telegram account');
    				}else{
    					$_member = $_member_q->fetch_assoc();
    					// validate member data
    					$verified = password_verify($this->response['text'], $_member['mpasswd']);
    					if(!$verified){
                            self::clearState();
    						$str .= __('FAILED, Check your username and password or maybe already registered with another telegram account');
    					}else{
    						//check account data
        					$query = sprintf('SELECT * FROM member_custom WHERE member_id = \'%s\' LIMIT 1',$_member['member_id']);
                            $username = trim($this->chat->first_name.' '.($this->chat->last_name??''));
        					$_mcq = DB::getInstance('mysqli')->query($query);    			
        					if($_mcq->num_rows){
        					    $_m_data = $_mcq->fetch_array();
        					    if($_m_data['telegram_id'] != NULL){
        					        self::clearState();
        					        return __('FAILED, Check your username and password or maybe already registered with another telegram account');
        					    }
        					$status = DB::getInstance('mysqli')->query("UPDATE member_custom SET telegram_id = '".$this->chat->id."', telegram_username = '".$username."' WHERE telegram_id IS NULL AND member_id='".$_member['member_id']."'"); //update if exist
        					}else{
        						$status = DB::getInstance('mysqli')->query("INSERT IGNORE INTO `member_custom` (`member_id`,`telegram_id`, `telegram_username`) VALUES ('".$_member['member_id']."','".$this->chat->id."','".$username."');");    
        					}
                            $this->keyboard = Keyboard::keyboardLayout(true);
    						return __('Account activation process is successful');
    					}
    				}
    				//delete state command
                    self::clearState();
    				return __('Status : ').$str;
    			}
    		}
    			
    		$_temp_data['password'] = $this->response['text'];
    		$data = serialize($_temp_data);
			@DB::getInstance('mysqli')->query("UPDATE telegram_chat_log SET data = '".$data."' WHERE chat_id='".$this->chat->id."'");
    		return __('Insert Your Password ! if you dont have one, please contact the library staff');
    	}
    	return false;
    }

    function loan()
    {
     	$this->keyboard = Keyboard::keyboardLayout($this->is_member);
    	if(!$this->is_member){
    		return __('Only for registered account');   		
    	}  
        self::clearState();  	
        $loan_data = CirculationLib::getLoan($this->member['member_id']);
        if($loan_data){
            $str_html = __('Current Loan').PHP_EOL;
            $str_html .= '======================='.PHP_EOL;            
            foreach ($loan_data as $data) {
                $str_html .= '┬─ <b>'.$data['title'].'</b>'.PHP_EOL;
                $str_html .= '├ '.__('Item Code').' : <i>'.$data['item_code'].'</i>'.PHP_EOL;
                $str_html .= '├ '.__('Loan Date').' : '.$data['loan_date'].PHP_EOL;
                $str_html .= '└ '.__('Return Date').' : '.$data['due_date'].$data['is_overdue'].PHP_EOL;
                if($data['is_overdue']){
                $str_html .= '└ <i>'.__('Note').' : '.__('Collection in overdue status. you are not self extend').'</i>'.PHP_EOL;
                }
            }
        }else{
            $str_html = __('No Loan Transaction');
        }
    	return $str_html;
    }

    function fines()
    {
     	$this->keyboard = Keyboard::keyboardLayout($this->is_member);
    	if(!$this->is_member){
    		return __('Only for registered account');   		
    	}
        self::clearState(); 
        $loan_data = CirculationLib::getFines($this->member['member_id']);
        if($loan_data){
            $str_html = __('Current Fines').PHP_EOL;
            $str_html .= '======================='.PHP_EOL;            
            foreach ($loan_data as $data) {
                $str_html .= '┬─ <b>'.__('Total').' : '.($data['debet']-$data['credit']).'</b>'.PHP_EOL;
                $str_html .= '├ '.__('Credit').' : <i>'.$data['credit'].'</i>'.PHP_EOL;
                $str_html .= '└ '.__('Debet').' : '.$data['debet'].PHP_EOL.PHP_EOL;
            }
        }else{
            $str_html = __('No Loan Transaction');
        }
        return $str_html;
    }    
    /**
     * @return string
     */
    protected function help()
    {
        self::clearState(); 
    	$this->keyboard = Keyboard::keyboardLayout($this->is_member);
    	$str  = __('You use the <b>HELP</b> menu').PHP_EOL;
        $str .= '======================='.PHP_EOL;
    	$str .= '▶️ '.__('<b>OPAC</b> is the link to the main page of this System').PHP_EOL;
    	$str .= '▶️ '.__('<b>Help</b> to provide a brief overview of the menus available in this service').PHP_EOL;
    	if(!$this->is_member){
    	$str .= '▶️ '.__('<b>Activation</b> is used to link your registered account in library membership. This registration process will record your Telegram account into our system').PHP_EOL;
    	}
    	else{
    	$str .= '▶️ '.__('<b>Loan</b> to display a list of your current loans').PHP_EOL;
    	$str .= '▶️ '.__('<b>Overdue</b> to display a list of your current overdues').PHP_EOL;
      	$str .= '▶️ '.__('<b>Fines</b> to display a list of your current fines').PHP_EOL;
    	$str .= '▶️ '.__('<b>Account</b> to display a detail of your account').PHP_EOL;
    	}
    	return $str;
    }

    protected function clearState()
    {
        $this->keyboard = Keyboard::keyboardLayout($this->is_member);
        @DB::getInstance('mysqli')->query("DELETE FROM telegram_chat_log WHERE chat_id='".$this->chat->id."'");
        return true;
    }

}