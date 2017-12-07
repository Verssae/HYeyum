<?php

/*
  Plugin Name: Naver Web Syndication v2
  Plugin URI: http://www.iamgood.co.kr
  Description: 네이버 웹문서 신디케이션v2
 * This is a plugin for built in board on the website
  Version: 1.1
  Author: iamgood
  Author URI: http://www.iamgood.co.kr
 */

/* Copyright 2014 Naver Syndication v2 (email : consult@iamgood.co.kr)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

define('NWS_PREFIX', 'nwsv2');

if (!defined('ABSPATH')) exit;

if (!class_exists('nws_Main_class')) :
    include_once 'class/nws_class.php';
    include_once 'class/nws_lib_class.php';
    include_once 'class/nws_dir_class.php';
    include_once 'class/nws_tpl_class.php';
    
    nws_Main_class::instance();
endif;
?>