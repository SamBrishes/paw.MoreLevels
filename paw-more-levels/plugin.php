<?php
/*
 |  PawMoreLevels - A small Bludit Hack to go a bit deeper!
 |  @file       ./plugin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 - Alpha
 |
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class PawMoreLevels extends Plugin{
        /*
         |  CONSTRUCTOR
         |  @since  0.1.1
         */
        public function __construct(){
            global $url, $pages;
            require_once("system/paw.pages.class.php");

            // Overwrite Pages
            $pages = new PawPages();
            parent::__construct();

            // Overwrite AJAX Call
            $slug = $url->explodeSlug();
            if($slug[0] === "ajax" && $slug[1] === "get-parents"){
                header("Content-Type: application/json");

                // Get Query
                $query = isset($_GET["query"])? Text::lowercase($_GET["query"]): false;
                if($query === false){
                    die(json_encode(array("status" => 1, "files" => "Invalid query.")));
                }

                // Current Page
                if(strpos($_SERVER["HTTP_REFERER"], DOMAIN_ADMIN . "edit-content") === 0){
                    $current = str_replace(DOMAIN_ADMIN . "edit-content/", "", $_SERVER["HTTP_REFERER"]);
                }

                // Get Pages
                $temp = array();
                foreach($pages->getParents() AS $parent){
                    $page = new Page($parent);
                    if(Text::stringContains(Text::lowercase($page->title()), $query)){
                        $temp[$page->key()] = $page->title();
                        if(substr_count($page->key(), "/") > 0){
                            $path = explode("/", $page->key());
                            $title = array();
                            while(count($path) > 1){
                                $getPage = new Page($path[0]);
                                $title[] = $getPage->title();
                                $path[0] = array_shift($path) . "/" . $path[0];
                            }
                            if(isset($current) && $page->key() == $current){
                                continue;
                            }
                            $temp[$page->key()] = implode(" / ", $title) . " / " . $temp[$page->key()];
                        }
                    }
                }
                die(json_encode(array_flip($temp)));
            }
        }

        /*
         |  HOOK :: ADMIN BODY END
         |  @since  0.1.0
         */
        public function adminBodyEnd(){
            $file = $this->domainPath() . "js/more-levels.js";
            return '<script type="text/javascript" src="'. $file .'"></script>';
        }
    }
