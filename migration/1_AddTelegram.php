<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 08/02/2021 18:50
 * @File name           : 1_AddTelegram.php
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

use SLiMS\DB;
use SLiMS\Migration\Migration;

class AddTelegram extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    function up()
    {
        DB::getInstance()->query("
            ALTER TABLE `member_custom` 
            CHANGE `member_id` `member_id` VARCHAR(20) 
            CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

            ALTER TABLE `member_custom` 
              ADD `telegram_id` varchar(32) COLLATE 'utf8_unicode_ci' NULL,
              ADD `telegram_username` varchar(64) COLLATE 'utf8_unicode_ci' NULL AFTER `telegram_id`;

            CREATE TABLE `telegram_settings` (
              `token` varchar(64) NOT NULL,
              `self_regis` tinyint(1) NOT NULL DEFAULT '1',
              `self_extend` tinyint(1) NOT NULL DEFAULT '1',
              `self_reserv` tinyint(1) NOT NULL DEFAULT '1',
              `circ_receipt` tinyint(1) NOT NULL DEFAULT '1'
            ) ENGINE='MyISAM' COLLATE 'utf8_unicode_ci';

            INSERT INTO `telegram_settings` (`token`, `self_regis`, `self_extend`, `self_reserv`,`circ_receipt`)
            VALUES ('', '1', '1', '1', '1');

            CREATE TABLE `telegram_answer` (
              `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `question` varchar(100) NULL,
              `response` text NULL,
              `input_date` datetime NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE='MyISAM' COLLATE 'utf8_unicode_ci';

            INSERT INTO `telegram_answer` (`question`, `response`, `input_date`)
            VALUES ('halo', 'hai, halo, halo juga, apa kabar', NULL);    

            CREATE TABLE `telegram_chat_log` (
              `chat_id` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
              `callback` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
              `data` text COLLATE utf8_unicode_ci
            ) ENGINE='MyISAM' COLLATE 'utf8_unicode_ci';                   

        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    function down()
    {
        DB::getInstance()->query("
            DROP TABLE `telegram_settings`;
            DROP TABLE `telegram_answer`;
            DROP TABLE `telegram_chat_log`;
            ALTER TABLE `member_custom` 
            DROP `telegram_id`,
            DROP `telegram_username`;

        ");
    }
}