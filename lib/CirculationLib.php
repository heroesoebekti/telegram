<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 08/02/2021 18:50
 * @File name           : CirculationLib.php
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
use Lib\simbio_date;


class CirculationLib{

    static public function getLoan($member_id)
    {
    $_q = DB::getInstance('mysqli')->query('SELECT b.title, i.item_code, l.loan_date, l.due_date FROM loan l 
            LEFT JOIN item i ON i.item_code=l.item_code 
            LEFT JOIN biblio b ON i.biblio_id=b.biblio_id 
            WHERE l.is_return = 0 AND i.item_code IS NOT NULL AND l.member_id = \''.$member_id.'\''); 
        if($_q->num_rows){
            while ($data = $_q->fetch_array()) {
                $data['is_overdue'] = $data['due_date']<=date("Y-m-d")?true:false;
                $loan_data[] = $data;
            }
            return $loan_data;
        }
        return false;    
    }

    static public function getFines($member_id)
    {
    $_q = DB::getInstance('mysqli')->query('SELECT sum(debet) as debet, sum(credit) as credit FROM fines WHERE member_id = \''.$member_id.'\'');
        if($_q->num_rows){
            while ($data = $_q->fetch_array()) {
                $fines_data[] = $data;
            }
            return $fines_data;
        }
        return false; 
    }

    static public function checkLoan($member_id, $item_code)
    {
        $_loan_data = self::itemLoanStatus($member_id, $item_code);
        $int_loan_id = $_loan_data['loan_id'];
        if($_loan_data){
            // check if member not pending or expired
            $_member_q = @DB::getInstance('mysqli')->query("SELECT * FROM member WHERE (is_pending = 1 OR expire_date < curdate()) AND member_id = '".$member_id."'");
            if($_member_q->num_rows){
                $msg = 'expire';
            }
            else{
                // check if this item overdue
                if($_loan_data['due_date'] <= date("Y-m-d")){
                    $msg = 'overdue';
                }else {
                    // check if this item is being reserved by other member
                    $_resv_q = @DB::getInstance('mysqli')->query("SELECT l.item_code FROM reserve AS rs
                        INNER JOIN loan AS l ON rs.item_code=l.item_code
                        WHERE l.loan_id=$int_loan_id AND rs.member_id!='" . $member_id . "'");
                    if ($_resv_q->num_rows) {
                        $msg = 'reserve';
                    } else {
                        $msg = $_loan_data;
                    }
                }
            }  
            return $msg;
        }
        return false;
    }

    static public function extend($member_id, $item_code)
    {
        $_loan_data = self::itemLoanStatus($member_id, $item_code);
        $int_loan_id = $_loan_data['loan_id'];
        $renewed     = $_loan_data['renewed'];
        $last_update = date_format(date_create($_loan_data['last_update']), "Y-m-d");

        // check loan rules
        $_loan_rules_q = @DB::getInstance('mysqli')->query("SELECT lr.loan_periode, lr.reborrow_limit FROM mst_loan_rules AS lr LEFT JOIN
            loan AS l ON lr.loan_rules_id=l.loan_rules_id WHERE loan_id=$int_loan_id");
        if ($_loan_rules_q->num_rows > 0) {
            $_loan_rules_d = $_loan_rules_q->fetch_row();
            $_loan_periode = $_loan_rules_d[0];
            $_reborrow_limit = $_loan_rules_d[1];
        }else{
            $_member_rules_q = @DB::getInstance('mysqli')->query("SELECT mmt.loan_periode, mmt.reborrow_limit FROM mst_member_type AS mmt LEFT JOIN
            member AS m ON m.member_type_id=mmt.member_type_id WHERE m.member_id='$member_id'");
            if($_member_rules_q->num_rows){
                $_loan_rules_d   = $_member_rules_q->fetch_row();
                $_loan_periode = $_loan_rules_d[0];
                $_reborrow_limit = $_loan_rules_d[1];                
            }
        }

        //get data member
        $member = api::member_load(DB::getInstance('mysqli'), $member_id??null);
        $_data_member = $member[0];

        // calculate due date
        $holiday = self::checkHoliday();
        $_loan_date = date('Y-m-d');
        $_due_date = simbio_date::getNextDate($_loan_periode, $_loan_date);
        $_due_date = simbio_date::getNextDateNotHoliday($_due_date, $holiday['holiday_dayname'], $holiday['holiday_date']);

        // check if due date is not more than member expiry date
        $_expiry_date_compare = simbio_date::compareDates($_due_date, $_data_member['expire_date']);
        if ($_expiry_date_compare != $_data_member['expire_date']) {
            $_due_date = $_data_member['expire_date'];
        }

        if($last_update == date("Y-m-d")){
            return 'on_update';
        }

        if($renewed >= $_reborrow_limit){
            return 'max_reborrow';
        }

        //update loan data
        $query = @DB::getInstance('mysqli')->query("UPDATE loan SET renewed=renewed+1, due_date='$_due_date', is_return=0, last_update= '".date("Y-m-d H:i:s")."'
            WHERE loan_id=$int_loan_id AND member_id='".$member_id."'");

        return 'success';
    }

    static function itemLoanStatus($member_id, $item_code)
    {
        $_l_q = @DB::getInstance('mysqli')->query(sprintf("SELECT b.title, i.item_code, l.loan_date,l.return_date,l.loan_id, l.due_date,l.renewed, l.last_update 
            FROM loan l 
            left join item i on l.item_code=i.item_code 
            left join biblio b on b.biblio_id=i.biblio_id
            WHERE l.member_id= '%s' AND l.item_code='%s' AND is_return=0",$member_id, $item_code));
        if($_l_q->num_rows){
            $_loan_data = $_l_q->fetch_assoc();
            return $_loan_data;
        }
        return false;
    }

    static function checkHoliday()
    {
        $data['holiday_dayname'] = array();
        $data['holiday_date']    = array();
        // load holiday data from database
        $_holiday_dayname_q = @DB::getInstance('mysqli')->query('SELECT holiday_dayname FROM holiday WHERE holiday_date IS NULL');
        while ($_holiday_dayname_d = $_holiday_dayname_q->fetch_row()) {
            $data['holiday_dayname'][] = $_holiday_dayname_d[0];
        }

        $_holiday_date_q = @DB::getInstance('mysqli')->query('SELECT holiday_date FROM holiday WHERE holiday_date IS NOT NULL
                ORDER BY holiday_date DESC LIMIT 365');
        while ($_holiday_date_d = $_holiday_date_q->fetch_row()) {
            $data['holiday_date'][$_holiday_date_d[0]] = $_holiday_date_d[0];
        }
        return $data;
    }

}