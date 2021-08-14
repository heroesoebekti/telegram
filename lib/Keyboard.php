<?php
/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 08/02/2021 18:50
 * @File name           : Keyboard.php
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


class Keyboard{

    static public function keyboardLayout($status = null)
    {
        global $sysconf;

        $url = ($_SERVER['HTTP_X_FORWARDED_PROTO']??$_SERVER['REQUEST_SCHEME']).'://'.$_SERVER['SERVER_NAME'].SWB;
        
        $kb =  [
            "inline_keyboard" => [
                [
                    [
                        "text" => "🌏 OPAC",
                        "url" => $url
                    ],
                    [
                        "text" => "❓ ".__("Help"),
                        "callback_data" => "#help"
                    ],
                    [
                        "text" => "📝 ".__("Membership"),
                        "callback_data" => "#account"
                    ]
                ],
                [
                    [
                        "text" => "📝 ".__("Loans"),
                        "callback_data" => "#loan"
                    ],
                    [
                        "text" => "📝 ".__("Extend"),
                        "callback_data" => "#extend"
                    ],
                    [
                        "text" => "📝 ".__("Fines"),
                        "callback_data" => "#fines"
                    ]
                ],
            ]
        ];

        if(!$status){
            $kb = [
                "inline_keyboard" => [
                    [
                        [
                            "text" => "🌏 OPAC",
                            "url" => 'https://slims.web.id'
                        ],
                        [
                            "text" => "🔐 ".__("Activate"),
                            "callback_data" => "#reg"
                        ],
                        [
                            "text" => "❓ ".__("Help"),
                            "callback_data" => "#help"
                        ]
                    ],
                ]
            ];
        }

        return json_encode($kb);
    }

    static public function unsetKeyboard(){
        return '';
    }
}