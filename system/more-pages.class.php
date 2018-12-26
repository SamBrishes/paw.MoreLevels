<?php
/*
 |  PawMorePages - A small Bludit Hack to go deeper!
 |  @file       ./system/more-pages.class.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 - Alpha
 |
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class PawPages extends Pages{
        /*
         |  OVERWRITE :: GENERATE KEY
         |  @since  1.0.0
         */
        public function generateKey($text, $parent = false, $slug = false, $old = ""){
            if($slug){
                return parent::generateKey($text, $parent, $slug, $old);
            }
            $parent = str_replace("/", "-02-2-20-", $parent);
            $return = parent::generateKey($text, $parent, $slug, $old);
            return str_replace("-02-2-20-", "/", $return);
        }

        /*
         |  OVERWRITE :: GET PARENTS
         |  @since  1.0.0
         */
        public function getParents(){
            return $this->getPublishedDB();
        }

        /*
         |  HELPER :: GET REAL NEXT PAGE
         |  @since  1.0.0
         */
        public function realNextPageKey($slug){
            $keys = $this->getPublishedDB();
            foreach($keys AS $key){
                if(strpos($key, $slug . "/") === false){
                    continue;
                }
                return $key;
            }
            return parent::previousPageKey($slug);
        }

        /*
         |  HELPER :: GET REAL PREVIOUS PAGE
         |  @since  1.0.0
         */
        public function realPreviousPageKey($slug){
            return parent::nextPageKey($slug);
        }
    }
